<?php

namespace Tests\Feature\Jobs;

use App\Jobs\EnviaSenhaPepJob;
use App\Mail\LinkSenhaInterlabNotification;
use App\Models\DadosGeraDoc;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnviaSenhaPepJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_email_e_atualiza_senha_enviada(): void
    {
        Mail::fake();

        $inscrito = $this->criarInscrito();
        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'tag_senha',
            'content' => [
                'participante_id' => $inscrito->id,
                'tag_senha' => 'TAG123',
                'interlab_nome' => 'PEP Teste',
            ],
        ]);

        $job = new EnviaSenhaPepJob(
            $dadosDoc->id,
            ['responsavel@example.com', 'lab@example.com'],
            $inscrito->id,
        );

        $job->handle();

        Mail::assertSent(LinkSenhaInterlabNotification::class, function (LinkSenhaInterlabNotification $mail) use ($dadosDoc) {
            return $mail->dadosDoc->id === $dadosDoc->id;
        });

        $inscrito->refresh();
        $this->assertNotNull($inscrito->senha_enviada);
    }

    public function test_reenvio_atualiza_senha_enviada_para_ultimo_envio(): void
    {
        Mail::fake();

        $inscrito = $this->criarInscrito();
        $envioAnterior = Carbon::parse('2024-01-01 10:00:00');
        $inscrito->update(['senha_enviada' => $envioAnterior]);

        $dadosDoc = DadosGeraDoc::query()->create([
            'tipo' => 'tag_senha',
            'content' => [
                'participante_id' => $inscrito->id,
                'tag_senha' => 'TAG456',
                'interlab_nome' => 'PEP Teste',
            ],
        ]);

        Carbon::setTestNow('2025-06-15 14:30:00');

        (new EnviaSenhaPepJob(
            $dadosDoc->id,
            ['responsavel@example.com'],
            $inscrito->id,
        ))->handle();

        $inscrito->refresh();

        $this->assertTrue($inscrito->senha_enviada->greaterThan($envioAnterior));
        $this->assertEquals('2025-06-15 14:30:00', $inscrito->senha_enviada->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    private function criarInscrito(): InterlabInscrito
    {
        $agenda = AgendaInterlabFactory::new()->create();
        $pessoa = PessoaFactory::new()->create(['email' => 'responsavel@example.com']);
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
            'email' => 'lab@example.com',
            'tag_senha' => 'TAG123',
            'senha_enviada' => null,
        ]);
    }
}
