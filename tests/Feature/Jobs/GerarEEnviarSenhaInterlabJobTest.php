<?php

namespace Tests\Feature\Jobs;

use App\Jobs\GerarEEnviarSenhaInterlabJob;
use App\Mail\LinkSenhaInterlabNotification;
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

class GerarEEnviarSenhaInterlabJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_gera_dados_doc_envia_email_e_atualiza_senha_enviada(): void
    {
        Mail::fake();

        $inscrito = $this->criarInscritoComTag('ABCDE');

        (new GerarEEnviarSenhaInterlabJob($inscrito->id))->handle();

        $this->assertDatabaseCount('dados_gera_doc', 1);

        Mail::assertSent(LinkSenhaInterlabNotification::class);

        $inscrito->refresh();
        $this->assertNotNull($inscrito->senha_enviada);
    }

    public function test_nao_atualiza_senha_enviada_quando_interlab_sem_tag(): void
    {
        Mail::fake();

        $inscrito = $this->criarInscritoComTag(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tag do interlab não encontrada');

        (new GerarEEnviarSenhaInterlabJob($inscrito->id))->handle();

        $inscrito->refresh();
        $this->assertNull($inscrito->senha_enviada);
        $this->assertDatabaseCount('dados_gera_doc', 0);
        Mail::assertNothingSent();
    }

    public function test_nao_reprocessa_quando_senha_ja_enviada(): void
    {
        Mail::fake();

        $inscrito = $this->criarInscritoComTag('ABCDE', [
            'senha_enviada' => Carbon::parse('2025-01-01 10:00:00'),
        ]);

        (new GerarEEnviarSenhaInterlabJob($inscrito->id))->handle();

        $this->assertDatabaseCount('dados_gera_doc', 0);
        Mail::assertNothingSent();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function criarInscritoComTag(?string $tag, array $overrides = []): InterlabInscrito
    {
        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => $tag]);

        $pessoa = PessoaFactory::new()->create(['email' => 'responsavel@example.com']);
        $empresa = PessoaFactory::new()->create();
        $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => fake()->company(),
        ]);

        return InterlabInscritoFactory::new()->create(array_merge([
            'agenda_interlab_id' => $agenda->id,
            'pessoa_id' => $pessoa->id,
            'empresa_id' => $empresa->id,
            'laboratorio_id' => $laboratorio->id,
            'email' => 'lab@example.com',
            'tag_senha' => 'ABCDE123',
            'senha_enviada' => null,
        ], $overrides));
    }
}
