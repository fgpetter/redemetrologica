<?php

namespace Tests\Feature;

use App\Mail\LinkSenhaInterlabNotification;
use App\Models\AgendaInterlab;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReenviarSenhasInterlabCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reenfileira_senhas_da_agenda_com_destinatarios_e_copia_sistema(): void
    {
        // Queue::fake() não preserva delay de Mail::later(); destinatários e CC são verificados aqui.
        Queue::fake();

        $agendaAlvo = AgendaInterlabFactory::new()->create();
        $outraAgenda = AgendaInterlabFactory::new()->create();

        $inscritoUm = $this->criarInscrito($agendaAlvo, 'responsavel1@example.com', 'lab1@example.com', 'TAG111');
        $inscritoDois = $this->criarInscrito($agendaAlvo, 'responsavel2@example.com', 'lab2@example.com', 'TAG222');
        $this->criarInscrito($outraAgenda, 'responsavel3@example.com', 'lab3@example.com', 'TAG333');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agendaAlvo->id])
            ->assertSuccessful();

        $this->assertDatabaseCount('dados_gera_doc', 2);

        Queue::assertPushed(SendQueuedMailable::class, 2);

        Queue::assertPushed(SendQueuedMailable::class, function (SendQueuedMailable $job) use ($inscritoUm) {
            if (! $job->mailable instanceof LinkSenhaInterlabNotification) {
                return false;
            }

            /** @var LinkSenhaInterlabNotification $mailable */
            $mailable = $job->mailable;

            if ($mailable->dadosDoc->content['participante_id'] !== $inscritoUm->id) {
                return false;
            }

            $this->assertTrue($mailable->hasTo('responsavel1@example.com'));
            $this->assertTrue($mailable->hasTo('lab1@example.com'));
            $this->assertTrue($mailable->hasCc('sistema@redemetrologica.com.br'));

            return true;
        });

        Queue::assertPushed(SendQueuedMailable::class, function (SendQueuedMailable $job) use ($inscritoDois) {
            if (! $job->mailable instanceof LinkSenhaInterlabNotification) {
                return false;
            }

            /** @var LinkSenhaInterlabNotification $mailable */
            $mailable = $job->mailable;

            if ($mailable->dadosDoc->content['participante_id'] !== $inscritoDois->id) {
                return false;
            }

            $this->assertTrue($mailable->hasTo('responsavel2@example.com'));
            $this->assertTrue($mailable->hasTo('lab2@example.com'));
            $this->assertTrue($mailable->hasCc('sistema@redemetrologica.com.br'));

            return true;
        });
    }

    /**
     * Com queue driver `database`, cada job fica na tabela `jobs` com `available_at` (epoch Unix)
     * indicando quando o worker pode processar. Para Mail::later(), o atraso pedido reflete em
     * `available_at - created_at`. Entre o 1º e o 2º envio do comando, a diferença de delays é
     * 60s − 30s, logo `available_at` do segundo menos o do primeiro deve ser ~30s.
     */
    public function test_reenvio_interlab_persiste_delays_em_jobs_available_at(): void
    {
        Config::set('queue.default', 'database');

        $agendaAlvo = AgendaInterlabFactory::new()->create();

        $this->criarInscrito($agendaAlvo, 'resp-a@example.com', 'lab-a@example.com', 'TAGA');
        $this->criarInscrito($agendaAlvo, 'resp-b@example.com', 'lab-b@example.com', 'TAGB');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agendaAlvo->id])
            ->assertSuccessful();

        $jobs = DB::table('jobs')->orderBy('id')->get();

        $this->assertCount(2, $jobs);

        foreach ($jobs as $job) {
            $this->assertStringContainsString('LinkSenhaInterlabNotification', $job->payload);
        }

        $primeiro = $jobs[0];
        $segundo = $jobs[1];

        $atrasoPrimeiroSegundos = $primeiro->available_at - $primeiro->created_at;
        $atrasoSegundoSegundos = $segundo->available_at - $segundo->created_at;

        $this->assertEqualsWithDelta(30, $atrasoPrimeiroSegundos, 2);
        $this->assertEqualsWithDelta(60, $atrasoSegundoSegundos, 2);

        $diferencaEntreDisponibilidades = $segundo->available_at - $primeiro->available_at;
        $this->assertEqualsWithDelta(30, $diferencaEntreDisponibilidades, 2);
    }

    private function criarInscrito(AgendaInterlab $agenda, string $emailPessoa, string $emailLaboratorio, string $tagSenha): InterlabInscrito
    {
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
        ]);
    }
}
