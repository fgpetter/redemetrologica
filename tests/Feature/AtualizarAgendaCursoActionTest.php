<?php

use App\Actions\AtualizarAgendaCursoAction;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function agendaReagendamento(string $data): AgendaCursos
{
    $curso = Curso::create(['descricao' => 'Curso reagendado', 'tipo_curso' => 'OFICIAL']);
    $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);

    return AgendaCursos::create([
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'status' => 'CONFIRMADO',
        'tipo_agendamento' => 'ONLINE',
        'data_inicio' => $data,
        'horario' => '08:00',
        'link_aula' => 'https://example.com/aula',
    ]);
}

it('preserva confirmações e invalida apenas reservas pendentes ao reagendar', function () {
    $agenda = agendaReagendamento('2026-11-15');
    $agenda->update([
        'conferencia_enviada_em' => now()->subHour(),
        'conferencia_envio_id' => 'reserva-conferencia-confirmada',
        'pendencias_envio_id' => 'reserva-pendencias-pendente',
        'informacoes_enviadas_em' => now()->subHour(),
    ]);

    $confirmado = $agenda->inscritos()->create([
        'pessoa_id' => PessoaFactory::new()->create()->id,
        'nome' => 'Confirmado',
        'email' => 'confirmado@example.com',
        'data_inscricao' => now(),
        'informacoes_enviadas_em' => now()->subHour(),
        'informacoes_envio_id' => 'reserva-antiga-confirmada',
    ]);
    $pendente = $agenda->inscritos()->create([
        'pessoa_id' => PessoaFactory::new()->create()->id,
        'nome' => 'Pendente',
        'email' => 'pendente@example.com',
        'data_inscricao' => now(),
        'informacoes_envio_id' => 'reserva-pendente',
    ]);

    app(AtualizarAgendaCursoAction::class)->execute($agenda, ['data_inicio' => '2026-11-20']);

    $agenda->refresh();

    expect($agenda->data_inicio->toDateString())->toBe('2026-11-20')
        ->and($agenda->conferencia_enviada_em)->not->toBeNull()
        ->and($agenda->conferencia_envio_id)->toBe('reserva-conferencia-confirmada')
        ->and($agenda->pendencias_enviadas_em)->toBeNull()
        ->and($agenda->pendencias_envio_id)->toBeNull()
        ->and($agenda->informacoes_enviadas_em)->not->toBeNull()
        ->and($confirmado->fresh()->informacoes_enviadas_em)->not->toBeNull()
        ->and($confirmado->fresh()->informacoes_envio_id)->toBe('reserva-antiga-confirmada')
        ->and($pendente->fresh()->informacoes_envio_id)->toBeNull();
});

it('não invalida reservas quando a data permanece a mesma', function () {
    $agenda = agendaReagendamento('2026-11-15');
    $inscrito = $agenda->inscritos()->create([
        'pessoa_id' => PessoaFactory::new()->create()->id,
        'nome' => 'Pendente',
        'email' => 'pendente@example.com',
        'data_inscricao' => now(),
        'informacoes_envio_id' => 'reserva-pendente',
    ]);
    $pendencias = 'reserva-pendencias';
    $agenda->update(['pendencias_envio_id' => $pendencias]);

    app(AtualizarAgendaCursoAction::class)->execute($agenda, ['data_inicio' => '2026-11-15']);

    expect($agenda->fresh()->pendencias_envio_id)->toBe($pendencias)
        ->and($inscrito->fresh()->informacoes_envio_id)->toBe('reserva-pendente');
});
