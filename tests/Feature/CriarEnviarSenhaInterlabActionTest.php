<?php

namespace Tests\Feature;

use App\Actions\CriarEnviarSenhaInterlabAction;
use App\Exceptions\InvalidEmailException;
use App\Jobs\EnviaSenhaPepJob;
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

class CriarEnviarSenhaInterlabActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_dados_gera_doc_e_dispara_job_com_destinatarios_validos(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP']);

        $inscrito = $this->criarInscritoCompleto($agenda, 'responsavel@example.com', 'lab@example.com');

        $dadosDoc = app(CriarEnviarSenhaInterlabAction::class)->execute($inscrito, 0);

        $this->assertNotNull($dadosDoc->id);
        $this->assertEquals('tag_senha', $dadosDoc->tipo);

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) use ($inscrito, $dadosDoc) {
            return $job->dadosGeraDocId === $dadosDoc->id
                && $job->inscritoId === $inscrito->id;
        });
    }

    public function test_envia_email_de_notificacao_quando_sem_destinatarios_validos(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP']);

        // Pessoa sem email e inscrito sem email → destinatarios vazios
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

        // Sem destinatários: alerta interno (InvalidEmailException instanciada, sem throw) e DadosGeraDoc criado
        $dadosDoc = app(CriarEnviarSenhaInterlabAction::class)->execute($inscrito, 0);

        $this->assertNotNull($dadosDoc->id);
        $this->assertEquals('tag_senha', $dadosDoc->tipo);

        // Nenhum job disparado porque não há destinatários
        Queue::assertNothingPushed();

        // Mail de notificação foi enviado pelo construtor da InvalidEmailException
        Mail::assertSent(\App\Mail\NotifyInvalidEmailException::class);
    }

    public function test_dispara_job_para_analista_quando_informado(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => 'PEP']);

        $inscrito = $this->criarInscritoCompleto($agenda, 'responsavel@example.com', 'lab@example.com');

        $analista = InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Teste',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'PEP-TAG-ANALISTA',
        ]);

        $dadosDoc = app(CriarEnviarSenhaInterlabAction::class)->execute($inscrito, 15, $analista);

        $this->assertNotNull($dadosDoc->id);
        $this->assertEquals('tag_senha', $dadosDoc->tipo);

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) use ($analista) {
            return $job->destinatarios === [$analista->email];
        });
    }

    private function criarInscritoCompleto(AgendaInterlab $agenda, string $emailPessoa, string $emailLaboratorio): InterlabInscrito
    {
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
            'tag_senha' => 'ABCDE123',
        ]);
    }
}
