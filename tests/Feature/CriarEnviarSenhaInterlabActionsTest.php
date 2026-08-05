<?php

namespace Tests\Feature;

use App\Actions\CriarEnviarSenhaAnalistaAction;
use App\Actions\CriarEnviarSenhaLaboratorioAction;
use App\Jobs\EnviaSenhaAnalistaJob;
use App\Jobs\EnviaSenhaLaboratorioJob;
use App\Models\AgendaInterlab;
use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CriarEnviarSenhaInterlabActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_laboratorio_cria_dados_gera_doc_e_dispara_job_com_destinatarios_validos(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP']);

        $inscrito = $this->criarInscritoCompleto($agenda, 'responsavel@example.com', 'lab@example.com');

        $dadosDoc = app(CriarEnviarSenhaLaboratorioAction::class)->execute($inscrito, 0);

        $this->assertNotNull($dadosDoc->id);
        $this->assertEquals('tag_senha', $dadosDoc->tipo);

        Queue::assertPushed(EnviaSenhaLaboratorioJob::class, function (EnviaSenhaLaboratorioJob $job) use ($inscrito, $dadosDoc) {
            return $job->dadosGeraDocId === $dadosDoc->id
                && $job->inscritoId === $inscrito->id
                && in_array('responsavel@example.com', $job->destinatarios, true)
                && in_array('lab@example.com', $job->destinatarios, true);
        });
    }

    public function test_laboratorio_notifica_quando_sem_destinatarios_validos(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP']);

        $pessoa = PessoaFactory::new()->create(['email' => null]);
        $empresa = PessoaFactory::new()->create();
        $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => fake()->company(),
        ]);

        $inscrito = InterlabInscritoFactory::new()->create([
            'agenda_interlab_id' => $agenda->id,
            'pessoa_id' => $pessoa->id,
            'empresa_id' => $empresa->id,
            'laboratorio_id' => $laboratorio->id,
            'email' => null,
            'tag_senha' => 'TAG123',
        ]);

        $dadosDoc = app(CriarEnviarSenhaLaboratorioAction::class)->execute($inscrito, 0);

        $this->assertNotNull($dadosDoc->id);
        $this->assertEquals('tag_senha', $dadosDoc->tipo);

        Queue::assertNothingPushed();
        Mail::assertSent(\App\Mail\NotifyInvalidEmailException::class);
    }

    public function test_laboratorio_gera_tag_senha_quando_ausente(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP']);

        $inscrito = $this->criarInscritoCompleto($agenda, 'responsavel@example.com', 'lab@example.com', null);

        app(CriarEnviarSenhaLaboratorioAction::class)->execute($inscrito, 0);

        $inscrito->refresh();
        $this->assertNotNull($inscrito->tag_senha);
        $this->assertStringStartsWith('PEP', $inscrito->tag_senha);
    }

    public function test_analista_cria_dados_gera_doc_e_dispara_job(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP', 'avaliacao' => 'ANALISTA']);

        $inscrito = $this->criarInscritoCompleto($agenda, 'responsavel@example.com', 'lab@example.com');

        $analista = InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Teste',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'PEP-TAG-ANALISTA',
        ]);

        $dadosDoc = app(CriarEnviarSenhaAnalistaAction::class)->execute($inscrito, $analista, 15);

        $this->assertNotNull($dadosDoc->id);
        $this->assertEquals('tag_senha_analista', $dadosDoc->tipo);
        $this->assertEquals('Analista Teste', $dadosDoc->content['analista_nome']);

        Queue::assertPushed(EnviaSenhaAnalistaJob::class, function (EnviaSenhaAnalistaJob $job) use ($analista) {
            return $job->destinatarios === [$analista->email]
                && $job->analistaId === $analista->id;
        });
    }

    private function criarInscritoCompleto(
        AgendaInterlab $agenda,
        string $emailPessoa,
        string $emailLaboratorio,
        ?string $tagSenha = 'ABCDE123',
    ): InterlabInscrito {
        $pessoa = PessoaFactory::new()->create(['email' => $emailPessoa]);
        $empresa = PessoaFactory::new()->create();
        $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => fake()->company(),
        ]);

        return InterlabInscritoFactory::new()->create([
            'agenda_interlab_id' => $agenda->id,
            'pessoa_id' => $pessoa->id,
            'empresa_id' => $empresa->id,
            'laboratorio_id' => $laboratorio->id,
            'email' => $emailLaboratorio,
            'tag_senha' => $tagSenha,
        ]);
    }
}
