<?php

namespace Tests\Feature;

use App\Jobs\ReenviarLinkSenhaInterlabJob;
use App\Models\AgendaInterlab;
use App\Models\DadosGeraDoc;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReenviarSenhasInterlabCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reenfileira_senhas_da_agenda_com_destinatario_copia_e_delay_incremental(): void
    {
        Queue::fake();

        $agendaAlvo = AgendaInterlabFactory::new()->create();
        $outraAgenda = AgendaInterlabFactory::new()->create();

        $inscritoUm = $this->criarInscrito($agendaAlvo, 'responsavel1@example.com', 'lab1@example.com', 'TAG111');
        $inscritoDois = $this->criarInscrito($agendaAlvo, 'responsavel2@example.com', 'lab2@example.com', 'TAG222');
        $this->criarInscrito($outraAgenda, 'responsavel3@example.com', 'lab3@example.com', 'TAG333');

        $this->artisan('app:reenviar-senhas-interlab', ['agenda_interlab_id' => $agendaAlvo->id])
            ->assertSuccessful();

        $this->assertDatabaseCount('dados_gera_doc', 2);

        $destinatariosEsperados = [
            $inscritoUm->id => 'responsavel1@example.com',
            $inscritoDois->id => 'responsavel2@example.com',
        ];
        $copiasEsperadas = [
            $inscritoUm->id => 'lab1@example.com',
            $inscritoDois->id => 'lab2@example.com',
        ];
        $delaysEsperados = [
            $inscritoUm->id => 30,
            $inscritoDois->id => 60,
        ];

        Queue::assertPushed(ReenviarLinkSenhaInterlabJob::class, 2);
        Queue::assertPushed(ReenviarLinkSenhaInterlabJob::class, function (ReenviarLinkSenhaInterlabJob $job) use ($destinatariosEsperados, $copiasEsperadas, $delaysEsperados) {
            $dadosDoc = DadosGeraDoc::query()->find($job->dadosDocId);

            $this->assertNotNull($dadosDoc);

            $participanteId = $dadosDoc->content['participante_id'];

            $this->assertSame($destinatariosEsperados[$participanteId], $job->emailDestinatario);
            $this->assertSame($copiasEsperadas[$participanteId], $job->emailCopia);

            $delaySegundos = now()->diffInSeconds($job->delay);
            $delayEsperado = $delaysEsperados[$participanteId];

            $this->assertGreaterThanOrEqual($delayEsperado - 1, $delaySegundos);
            $this->assertLessThanOrEqual($delayEsperado, $delaySegundos);

            return true;
        });
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
