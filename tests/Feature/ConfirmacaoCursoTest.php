<?php

use App\Actions\AtualizarAgendaCursoAction;
use App\Actions\EnfileirarConfirmacaoCursoAction;
use App\Actions\SalvaInscritoAction;
use App\Actions\SalvaInscritoInCompanyAction;
use App\Jobs\EnviarConfirmacaoCursoJob;
use App\Mail\ConfirmacaoCursoMail;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

function agendaConfirmacaoCurso(string $tipo = 'ONLINE'): AgendaCursos
{
    $curso = Curso::create(['descricao' => 'Metrologia', 'tipo_curso' => 'OFICIAL']);
    $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);

    return AgendaCursos::create([
        'curso_id' => $curso->id, 'instrutor_id' => $instrutor->id,
        'status' => 'REAGENDAR', 'tipo_agendamento' => $tipo,
        'data_inicio' => '2026-12-15', 'horario' => '08:00',
    ]);
}

function inscritoConfirmacaoCurso(AgendaCursos $agenda, string $email = 'inscrito@example.com')
{
    return $agenda->inscritos()->create([
        'pessoa_id' => PessoaFactory::new()->create(['email' => $email])->id,
        'nome' => 'Participante', 'email' => $email, 'data_inscricao' => now(),
    ]);
}

it('confirma todos os inscritos uma vez em todas as modalidades sem exigir pagamento', function ($tipo) {
    Bus::fake();
    $agenda = agendaConfirmacaoCurso($tipo);
    $primeiro = inscritoConfirmacaoCurso($agenda);
    inscritoConfirmacaoCurso($agenda, 'outro@example.com');
    $action = app(AtualizarAgendaCursoAction::class);
    $action->execute($agenda, ['status' => 'CONFIRMADO']);
    $action->execute($agenda, ['status' => 'CONFIRMADO']);
    $action->execute($agenda, ['status' => 'CANCELADO']);
    $action->execute($agenda, ['status' => 'CONFIRMADO']);

    Bus::assertDispatchedTimes(EnviarConfirmacaoCursoJob::class, 2);
    Bus::assertDispatched(EnviarConfirmacaoCursoJob::class, fn ($job) => $job->inscritoId === $primeiro->id
        && $job->email === [$primeiro->email] && $job->delay->isFuture());
    expect($primeiro->fresh()->confirmacao_agendada_em)->not->toBeNull();
})->with(['ONLINE', 'EVENTO', 'IN-COMPANY']);

it('pula email inválido e continua com os demais', function () {
    Bus::fake();
    Log::shouldReceive('channel')->with('validation')->twice()->andReturnSelf();
    Log::shouldReceive('warning')->twice();
    $agenda = agendaConfirmacaoCurso();
    $invalido = inscritoConfirmacaoCurso($agenda, 'invalido');
    inscritoConfirmacaoCurso($agenda);
    app(AtualizarAgendaCursoAction::class)->execute($agenda, ['status' => 'CONFIRMADO']);
    Bus::assertDispatchedTimes(EnviarConfirmacaoCursoJob::class, 1);
    expect($invalido->fresh()->confirmacao_agendada_em)->toBeNull();
});

it('processa uma única vez mesmo após mudança de status e usa o template informado', function () {
    Bus::fake();
    Mail::fake();
    $agenda = agendaConfirmacaoCurso();
    $inscrito = inscritoConfirmacaoCurso($agenda);
    app(AtualizarAgendaCursoAction::class)->execute($agenda, ['status' => 'CONFIRMADO']);
    $job = Bus::dispatched(EnviarConfirmacaoCursoJob::class)->first();
    $agenda->update(['status' => 'CANCELADO']);
    $job->handle();
    $job->handle();
    Mail::assertSent(ConfirmacaoCursoMail::class, 1);
    Mail::assertSent(ConfirmacaoCursoMail::class, fn ($mail) => $mail->hasTo($inscrito->email));
    expect($inscrito->fresh()->confirmacao_enviada_em)->not->toBeNull();
    $mail = new ConfirmacaoCursoMail($job->curso, $job->dataInicio);
    expect($mail->envelope()->subject)->toBe('Confirmação de Realização - Metrologia');
    $mail->assertSeeInHtml('Informamos que o Curso Metrologia em 15/12/2026 está confirmado.');
    $mail->assertSeeInHtml('O acesso será encaminhado por e-mail até dois dias antes do treinamento.');
    $mail->assertSeeInHtml('Procederemos com o envio da cobrança relativa à sua inscrição.');
});

it('persiste o job e a reserva atomicamente e desfaz ambos no rollback', function () {
    $agenda = agendaConfirmacaoCurso();
    $agenda->update(['status' => 'CONFIRMADO']);
    $inscrito = inscritoConfirmacaoCurso($agenda);
    DB::beginTransaction();
    app(EnfileirarConfirmacaoCursoAction::class)->execute($inscrito);
    expect(DB::table('jobs')->count())->toBe(1);
    DB::rollBack();
    expect(DB::table('jobs')->count())->toBe(0)
        ->and($inscrito->fresh()->confirmacao_agendada_em)->toBeNull();
});

it('envia ao concluir inscrição em treinamento confirmado inclusive in company', function ($tipo, $confirmado) {
    Bus::fake();
    Mail::fake();
    $agenda = agendaConfirmacaoCurso($tipo);
    if ($confirmado) {
        $agenda->update(['status' => 'CONFIRMADO']);
    }
    $pessoa = PessoaFactory::new()->create();
    $user = User::factory()->create();
    $pessoa->update(['user_id' => $user->id]);
    $this->actingAs($user);

    if ($tipo === 'IN-COMPANY') {
        $inscrito = app(SalvaInscritoInCompanyAction::class)->criar($agenda, 'Novo', 'novo@example.com', null);
    } else {
        $inscrito = app(SalvaInscritoAction::class)->criar($agenda, [
            'tipo_inscricao' => 'cpf', 'idempotente' => true, 'pessoa_id' => $pessoa->id,
            'nome' => 'Novo', 'email' => 'novo@example.com', 'valor' => 100,
        ], enviarEmail: false);
    }

    Bus::assertDispatchedTimes(EnviarConfirmacaoCursoJob::class, $confirmado ? 1 : 0);
    expect($inscrito->fresh()->confirmacao_agendada_em !== null)->toBe($confirmado);
})->with([['ONLINE', true], ['EVENTO', true], ['IN-COMPANY', true], ['ONLINE', false], ['IN-COMPANY', false]]);

it('processa o job persistido pela fila database', function () {
    Mail::fake();
    $agenda = agendaConfirmacaoCurso();
    $agenda->update(['status' => 'CONFIRMADO']);
    $inscrito = inscritoConfirmacaoCurso($agenda);
    app(EnfileirarConfirmacaoCursoAction::class)->execute($inscrito);
    app(EnfileirarConfirmacaoCursoAction::class)->execute($inscrito);
    expect(DB::table('jobs')->count())->toBe(1);
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true])->assertSuccessful();
    Mail::assertSent(ConfirmacaoCursoMail::class, 1);
    expect(DB::table('jobs')->count())->toBe(0)
        ->and($inscrito->fresh()->confirmacao_enviada_em)->not->toBeNull();
});

it('inclui o responsável e elimina endereços repetidos', function ($emailResponsavel, $esperados) {
    Bus::fake();
    Mail::fake();
    $agenda = agendaConfirmacaoCurso();
    $inscrito = inscritoConfirmacaoCurso($agenda);
    $inscrito->pessoa->update(['email' => $emailResponsavel]);
    app(AtualizarAgendaCursoAction::class)->execute($agenda, ['status' => 'CONFIRMADO']);
    $job = Bus::dispatched(EnviarConfirmacaoCursoJob::class)->first();
    expect($job->email)->toBe($esperados);
    $job->handle();
    $job->handle();
    Mail::assertSent(ConfirmacaoCursoMail::class, 1);
    Mail::assertSent(ConfirmacaoCursoMail::class, function ($mail) use ($esperados) {
        return count($mail->to) === count($esperados)
            && collect($esperados)->every(fn ($email) => $mail->hasTo($email));
    });
})->with([
    ['responsavel@example.com', ['inscrito@example.com', 'responsavel@example.com']],
    ['inscrito@example.com', ['inscrito@example.com']],
    ['INSCRITO@example.com', ['inscrito@example.com']],
]);
