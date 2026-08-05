<?php

namespace Tests\Feature\Commands;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessarSenhasInterlabConfirmadoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_enfileira_job_para_inscrito_elegivel(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com');

        $this->artisan('app:processar-senhas-interlab-confirmado')
            ->assertSuccessful();

        Queue::assertPushed(EnviaSenhaLaboratorioJob::class, function (EnviaSenhaLaboratorioJob $job) use ($inscrito) {
            return $job->inscritoId === $inscrito->id;
        });
    }

    public function test_enfileira_job_para_analista_elegivel(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE', 'avaliacao' => 'ANALISTA']);

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com');

        $analista = InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Teste',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'ABCDE1234',
            'senha_enviada' => null,
        ]);

        $this->artisan('app:processar-senhas-interlab-confirmado')
            ->assertSuccessful();

        Queue::assertNotPushed(EnviaSenhaLaboratorioJob::class);
        Queue::assertPushed(EnviaSenhaAnalistaJob::class, function (EnviaSenhaAnalistaJob $job) use ($analista) {
            return $job->analistaId === $analista->id
                && $job->destinatarios === ['analista@example.com'];
        });
    }

    public function test_nao_enfileira_quando_agenda_nao_confirmada(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'AGENDADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com');

        $this->artisan('app:processar-senhas-interlab-confirmado')
            ->assertSuccessful();

        Queue::assertNotPushed(EnviaSenhaLaboratorioJob::class);
    }

    public function test_nao_enfileira_quando_senha_ja_enviada(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com', [
            'senha_enviada' => Carbon::parse('2025-01-01 10:00:00'),
        ]);

        $this->artisan('app:processar-senhas-interlab-confirmado')
            ->assertSuccessful();

        Queue::assertNotPushed(EnviaSenhaLaboratorioJob::class);
    }

    public function test_nao_enfileira_analista_quando_senha_ja_enviada(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE', 'avaliacao' => 'ANALISTA']);

        $inscrito = $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com');

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Teste',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'ABCDE1234',
            'senha_enviada' => Carbon::parse('2025-01-01 10:00:00'),
        ]);

        $this->artisan('app:processar-senhas-interlab-confirmado')
            ->assertSuccessful();

        Queue::assertNotPushed(EnviaSenhaAnalistaJob::class);
    }

    public function test_nao_enfileira_quando_interlab_sem_tag(): void
    {
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => null]);

        $this->criarInscrito($agenda, 'resp@example.com', 'lab@example.com');

        $this->artisan('app:processar-senhas-interlab-confirmado')
            ->assertSuccessful();

        Queue::assertNotPushed(EnviaSenhaLaboratorioJob::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function criarInscrito(
        AgendaInterlab $agenda,
        string $emailPessoa,
        string $emailLaboratorio,
        array $overrides = [],
    ): InterlabInscrito {
        $pessoa = PessoaFactory::new()->create(['email' => $emailPessoa]);
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
            'email' => $emailLaboratorio,
            'tag_senha' => 'ABCDE123',
            'senha_enviada' => null,
        ], $overrides));
    }
}
