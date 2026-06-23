<?php

namespace Tests\Feature\InscricaoInterlab;

use App\Actions\NotifyInscricaoInterlabAction;
use App\Jobs\EnviaSenhaPepJob;
use App\Mail\NotifyInvalidEmailException;
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

class NotifyInscricaoInterlabActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispara_envio_de_senha_quando_agenda_confirmada_e_nova_inscricao(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $inscrito = $this->criarInscritoCompleto($agenda);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $agenda);

        Queue::assertPushed(EnviaSenhaPepJob::class, function (EnviaSenhaPepJob $job) use ($inscrito) {
            return $job->inscritoId === $inscrito->id
                && in_array('responsavel@example.com', $job->destinatarios, true)
                && in_array('lab@example.com', $job->destinatarios, true);
        });
    }

    public function test_nao_dispara_envio_de_senha_quando_agenda_nao_confirmada(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'AGENDADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $inscrito = $this->criarInscritoCompleto($agenda);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $agenda);

        Queue::assertNotPushed(EnviaSenhaPepJob::class);
    }

    public function test_nao_dispara_envio_de_senha_em_edicao(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $inscrito = $this->criarInscritoCompleto($agenda);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $agenda, $inscrito->id);

        Queue::assertNotPushed(EnviaSenhaPepJob::class);
    }

    public function test_nao_dispara_envio_de_senha_sem_tag_do_interlab(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => null]);

        $inscrito = $this->criarInscritoCompleto($agenda);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $agenda);

        Queue::assertNotPushed(EnviaSenhaPepJob::class);
    }

    public function test_notifica_email_invalido_quando_pessoa_sem_email(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $inscrito = $this->criarInscritoComPessoaSemEmail($agenda);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $agenda);

        // Alerta interno por e-mail ausente; fluxo segue (sem throw)
        // A execução continua e tenta dispatcher EnviaSenhaPepJob
        Mail::assertSent(NotifyInvalidEmailException::class);
    }

    public function test_notifica_email_invalido_quando_analista_sem_email(): void
    {
        Mail::fake();
        Queue::fake();

        $agenda = AgendaInterlabFactory::new()->create(['status' => 'CONFIRMADO']);
        $agenda->interlab->update(['tag' => 'ABCDE']);

        $inscrito = $this->criarInscritoCompleto($agenda);

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Sem Email',
            'email' => '',
            'telefone' => '11999999999',
        ]);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $agenda);

        // Alerta interno por e-mail ausente; fluxo segue (sem throw)
        Mail::assertSent(NotifyInvalidEmailException::class);
    }

    private function criarInscritoComPessoaSemEmail(AgendaInterlab $agenda): InterlabInscrito
    {
        $pessoa = PessoaFactory::new()->create(['email' => null]);
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
            'tag_senha' => 'ABCDE123',
        ]);
    }

    private function criarInscritoCompleto(AgendaInterlab $agenda): InterlabInscrito
    {
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
            'tag_senha' => 'ABCDE123',
        ]);
    }
}
