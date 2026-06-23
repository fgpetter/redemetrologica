<?php

namespace Tests\Feature;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReenviarSenhasInterlabCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reenfileira_senhas_da_agenda_com_destinatarios_corretos(): void
    {
        Queue::fake();

        $agendaAlvo = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agendaAlvo);
        $outraAgenda = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($outraAgenda);

        $inscritoUm = $this->criarInscrito($agendaAlvo, 'responsavel1@example.com', 'lab1@example.com', 'TAG111');
        $inscritoDois = $this->criarInscrito($agendaAlvo, 'responsavel2@example.com', 'lab2@example.com', 'TAG222');
        $this->criarInscrito($outraAgenda, 'responsavel3@example.com', 'lab3@example.com', 'TAG333');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agendaAlvo->id])
            ->assertSuccessful();

        $this->assertDatabaseCount('dados_gera_doc', 2);

        Queue::assertPushed(EnviaSenhaPepJob::class, 2);

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) use ($inscritoUm) {
            return $job->inscritoId === $inscritoUm->id
                && in_array('responsavel1@example.com', $job->destinatarios, true)
                && in_array('lab1@example.com', $job->destinatarios, true);
        });

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) use ($inscritoDois) {
            return $job->inscritoId === $inscritoDois->id
                && in_array('responsavel2@example.com', $job->destinatarios, true)
                && in_array('lab2@example.com', $job->destinatarios, true);
        });
    }

    /**
     * Com queue driver `database`, cada job fica na tabela `jobs` com `available_at` (epoch Unix)
     * indicando quando o worker pode processar. Para dispatch com delay, o atraso pedido reflete em
     * `available_at - created_at`. Entre o 1º e o 2º envio do comando, a diferença de delays é
     * 60s − 30s, logo `available_at` do segundo menos o do primeiro deve ser ~30s.
     */
    public function test_reenvio_interlab_persiste_delays_em_jobs_available_at(): void
    {
        Config::set('queue.default', 'database');

        $agendaAlvo = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agendaAlvo);

        $this->criarInscrito($agendaAlvo, 'resp-a@example.com', 'lab-a@example.com', 'TAGA');
        $this->criarInscrito($agendaAlvo, 'resp-b@example.com', 'lab-b@example.com', 'TAGB');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agendaAlvo->id])
            ->assertSuccessful();

        $jobs = DB::table('jobs')->orderBy('id')->get();

        $this->assertCount(2, $jobs);

        foreach ($jobs as $job) {
            $this->assertStringContainsString('EnviaSenhaPepJob', $job->payload);
        }

        $primeiro = $jobs[0];
        $segundo = $jobs[1];

        $atrasoPrimeiroSegundos = $primeiro->available_at - $primeiro->created_at;
        $atrasoSegundoSegundos = $segundo->available_at - $segundo->created_at;

        $this->assertEqualsWithDelta(30, $atrasoPrimeiroSegundos, 2);
        $this->assertEqualsWithDelta(60, $atrasoSegundoSegundos, 2);

        $diferecaEntreDisponibilidades = $segundo->available_at - $primeiro->available_at;
        $this->assertEqualsWithDelta(30, $diferecaEntreDisponibilidades, 2);
    }

    public function test_lab_gera_tag_senha_quando_null_e_enfileira_envio(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agenda, 'PEP');

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', null);

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agenda->id])
            ->assertSuccessful();

        $inscrito->refresh();

        $this->assertNotNull($inscrito->tag_senha);
        $this->assertStringStartsWith('PEP', $inscrito->tag_senha);

        Queue::assertPushed(EnviaSenhaPepJob::class, 1);
    }

    public function test_lab_atualiza_senha_enviada_apos_handle_do_job(): void
    {
        Mail::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agenda, 'PEP');

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', null);

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agenda->id])
            ->assertSuccessful();

        $dadosDoc = $inscrito->fresh()->tagSenhaDoc;
        $this->assertNotNull($dadosDoc);

        (new EnviaSenhaPepJob(
            $dadosDoc->id,
            ['resp@example.com', 'lab@example.com'],
            $inscrito->id,
        ))->handle();

        $this->assertNotNull($inscrito->fresh()->senha_enviada);
    }

    public function test_lab_reenvia_sem_alterar_tag_existente(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agenda);

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', 'TAG-FIXA');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agenda->id])
            ->assertSuccessful();

        $this->assertSame('TAG-FIXA', $inscrito->fresh()->tag_senha);
        Queue::assertPushed(EnviaSenhaPepJob::class, 1);
    }

    public function test_lab_ignora_quando_tag_e_senha_enviada_preenchidos(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agenda);

        $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', 'TAG-OK', Carbon::parse('2025-01-01 10:00:00'));

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agenda->id])
            ->assertSuccessful()
            ->expectsOutputToContain('Ignorados 1');

        Queue::assertNothingPushed();
    }

    public function test_sem_interlab_tag_nao_enfileira_envios(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $agenda->load('interlab');
        $agenda->interlab->update(['tag' => null]);

        $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', 'TAG111');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agenda->id])
            ->assertSuccessful()
            ->expectsOutputToContain('Sem tag interlab 1');

        Queue::assertNothingPushed();
    }

    public function test_analista_gera_tag_pendente_e_envia_para_email_do_analista(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create();
        $this->garantirInterlabComTag($agenda, 'PEP', 'ANALISTA');

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', 'TAG-LAB-IGNORADA');

        $analistaSemTag = InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista A',
            'email' => 'analista-a@example.com',
            'telefone' => '11999999999',
            'tag_senha' => null,
        ]);

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista B',
            'email' => 'analista-b@example.com',
            'telefone' => '11888888888',
            'tag_senha' => 'PEP9999',
        ]);

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agenda->id])
            ->assertSuccessful();

        $analistaSemTag->refresh();

        $this->assertNotNull($analistaSemTag->tag_senha);
        $this->assertStringStartsWith('PEP', $analistaSemTag->tag_senha);

        Queue::assertPushed(EnviaSenhaPepJob::class, 2);

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) {
            return $job->destinatarios === ['analista-a@example.com'];
        });

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) {
            return $job->destinatarios === ['analista-b@example.com'];
        });
    }

    private function garantirInterlabComTag(
        AgendaInterlab $agenda,
        string $tag = 'PEP',
        ?string $avaliacao = null,
    ): void {
        $agenda->load('interlab');

        $dados = ['tag' => $tag];

        if ($avaliacao !== null) {
            $dados['avaliacao'] = $avaliacao;
        }

        $agenda->interlab->update($dados);
    }

    private function criarInscrito(
        AgendaInterlab $agenda,
        string $emailPessoa,
        string $emailLaboratorio,
        ?string $tagSenha,
        ?Carbon $senhaEnviada = null,
    ): InterlabInscrito {
        $pessoa = PessoaFactory::new()->create([
            'email' => $emailPessoa,
        ]);
        $empresa = PessoaFactory::new()->create();
        $endereco = Endereco::query()->create([
            'pessoa_id' => $empresa->id,
        ]);
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
            'senha_enviada' => $senhaEnviada,
        ]);
    }
}
