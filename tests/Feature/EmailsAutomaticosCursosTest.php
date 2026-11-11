<?php

use App\Actions\EnfileirarConferenciaCursoAction;
use App\Actions\EnfileirarEmailInscritoCursoAction;
use App\Mail\AvisoConferenciaTreinamentoMail;
use App\Mail\AvisoPendenciasTreinamentoMail;
use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use Carbon\Carbon;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function mailerArray(): MailManager
{
    return new MailManager(app());
}

function estadoEmail(string $table, int $id, string $chave): array
{
    $tipo = $table === 'curso_inscritos' ? 'participante' : explode(':', $chave)[0];
    $enviado = match ($tipo) {
        'participante' => 'informacoes_enviadas_em',
        'conferencia' => 'conferencia_enviada_em',
        'pendencias' => 'pendencias_enviadas_em',
    };
    $registro = DB::table($table)->where('id', $id)->first();

    return ['enviado_em' => $registro->$enviado];
}

function agendaEmail(string $data, array $extra = []): AgendaCursos
{
    $curso = Curso::create(['descricao' => 'Curso exemplo', 'tipo_curso' => 'OFICIAL']);
    $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);

    return AgendaCursos::create(array_merge(['curso_id' => $curso->id, 'instrutor_id' => $instrutor->id, 'status' => 'CONFIRMADO', 'tipo_agendamento' => 'ONLINE', 'data_inicio' => $data, 'horario' => '08:00', 'link_aula' => 'https://example.com/aula'], $extra));
}

it('agenda aviso de conferência inclusive no fim de semana sem duplicar', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    agendaEmail('2026-11-16', ['status' => 'AGENDADO']);
    agendaEmail('2026-11-16', ['tipo_agendamento' => 'EVENTO']);
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    Mail::assertQueued(AvisoConferenciaTreinamentoMail::class, 2);
    $mailable = Mail::queued(AvisoConferenciaTreinamentoMail::class)->first();
    expect($mailable->send(mailerArray()->mailer('array')))->not->toBeNull()
        ->and(estadoEmail('agenda_cursos', $agenda->id, 'conferencia:2026-11-16')['enviado_em'])->not->toBeNull();
});

it('avisa pendências na véspera e não envia aos inscritos', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    agendaEmail('2026-11-15', ['link_aula' => null]);
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    Mail::assertQueued(AvisoPendenciasTreinamentoMail::class, fn ($mail) => $mail->hasTo('administrativo@redemetrologica.com.br'));
    Mail::assertNotQueued(InformacoesTreinamentoMail::class);
    $mailable = Mail::queued(AvisoPendenciasTreinamentoMail::class)->first();
    expect($mailable->send(mailerArray()->mailer('array')))->not->toBeNull();
});

it('envia a cada inscrito sem depender de pagamento e ignora execução repetida do job', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15');
    $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/material.pdf', 'descricao' => 'Apostila']);
    foreach (['a@example.com', 'b@example.com'] as $email) {
        $pessoa = PessoaFactory::new()->create();
        $agenda->inscritos()->create(['pessoa_id' => $pessoa->id, 'nome' => 'Participante', 'email' => $email, 'data_inscricao' => now()]);
    }
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    Mail::assertQueued(InformacoesTreinamentoMail::class, 4);
    Mail::assertNotQueued(AvisoConferenciaTreinamentoMail::class);
    foreach (Mail::queued(InformacoesTreinamentoMail::class) as $email) {
        $email->send(mailerArray()->mailer('array'));
        expect($email->send(mailerArray()->mailer('array')))->toBeNull();
    }
    expect(DB::table('curso_inscritos')->whereNotNull('informacoes_enviadas_em')->count())->toBe(2);
});

it('atualiza timestamps e registra atividades ao enfileirar e enviar emails', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 05:00:00', 'America/Sao_Paulo'));
    $administrativa = agendaEmail('2026-11-16');
    $agenda = agendaEmail('2026-11-15');
    $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/material.pdf', 'descricao' => 'Apostila']);
    $inscrito = $agenda->inscritos()->create(['pessoa_id' => PessoaFactory::new()->create()->id, 'nome' => 'Ana', 'email' => 'ana@example.com', 'data_inscricao' => now()]);
    $logs = DB::table('activity_log')->count();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(DB::table('activity_log')->count())->toBe($logs);

    $this->travelTo(Carbon::parse('2026-11-14 06:01:00', 'America/Sao_Paulo'));
    foreach (Mail::queued(AvisoConferenciaTreinamentoMail::class)->concat(Mail::queued(InformacoesTreinamentoMail::class)) as $job) {
        $job->send(mailerArray()->mailer('array'));
    }

    expect(DB::table('activity_log')->count())->toBe($logs + 2)
        ->and($administrativa->fresh()->updated_at->equalTo(now()))->toBeTrue()
        ->and($inscrito->fresh()->updated_at->equalTo(now()))->toBeTrue()
        ->and($administrativa->fresh()->conferencia_enviada_em)->not->toBeNull()
        ->and($inscrito->fresh()->informacoes_enviadas_em)->not->toBeNull();
});

it('registra o job uma única vez na fila database', function () {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    agendaEmail('2026-11-16');
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(DB::table('jobs')->count())->toBe(2);

});

it('reverte o registro em caso de falha da fila', function () {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    config(['queue.connections.database.table' => 'fila_inexistente']);
    $this->artisan('cursos:emails-automaticos')->assertFailed();
    expect(DB::table('agenda_cursos')->where('id', $agenda->id)->value('conferencia_enviada_em'))->toBeNull();
});

it('prossegue para outras agendas se uma falhar no enfileiramento', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $primeira = agendaEmail('2026-11-16');
    $segunda = agendaEmail('2026-11-16');
    $this->app->instance(EnfileirarConferenciaCursoAction::class, new class($primeira->id) extends EnfileirarConferenciaCursoAction
    {
        public function __construct(private int $falhaId) {}

        public function execute(AgendaCursos $agenda, string $data): void
        {
            if ($agenda->id === $this->falhaId) {
                throw new RuntimeException('Falha simulada');
            }
            parent::execute($agenda, $data);
        }
    });
    $this->artisan('cursos:emails-automaticos')->assertFailed();
    Mail::assertQueued(AvisoConferenciaTreinamentoMail::class, 1);
});

function prepararEmailParticipante(): array
{
    $agenda = agendaEmail('2026-11-15');
    $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/material.pdf', 'descricao' => 'Apostila']);
    $inscrito = $agenda->inscritos()->create(['pessoa_id' => PessoaFactory::new()->create()->id, 'nome' => 'Ana', 'email' => 'ana@example.com', 'data_inscricao' => now()]);
    test()->artisan('cursos:emails-automaticos')->assertSuccessful();

    return [$agenda, $inscrito, Mail::queued(InformacoesTreinamentoMail::class)->first(fn ($job) => $job->inscrito->id === $inscrito->id)];
}

it('ignora e registra email inválido ou vazio sem impedir outro inscrito', function (string $email) {
    Mail::fake();
    Log::spy();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    [$agenda, $inscrito, $id] = prepararEmailParticipante();
    $inscrito->update(['email' => $email]);
    $outro = $agenda->inscritos()->create(['pessoa_id' => PessoaFactory::new()->create()->id, 'nome' => 'Outro', 'email' => 'outro@example.com', 'data_inscricao' => now()]);
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    Mail::queued(InformacoesTreinamentoMail::class)->first(fn ($job) => $job->inscrito->id === $outro->id)->send(mailerArray()->mailer('array'));
    Log::shouldHaveReceived('warning')->once();
    expect(DB::table('curso_inscritos')->whereNotNull('informacoes_enviadas_em')->count())->toBe(1);
})->with(['invalido', '']);

it('renderiza os conteúdos dos emails com data, materiais e pendências registradas', function () {
    $agenda = agendaEmail('2026-11-15');
    $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/material.pdf', 'descricao' => 'Apostila']);
    $inscrito = $agenda->inscritos()->create(['pessoa_id' => PessoaFactory::new()->create()->id, 'nome' => 'Ana', 'email' => 'ana@example.com', 'data_inscricao' => now()]);
    $agenda->load(['curso', 'materiaisTreinamento']);
    $participante = new InformacoesTreinamentoMail($agenda, $inscrito);
    $participante->assertSeeInHtml('ANA')->assertSeeInHtml('15/11/2026')->assertSeeInHtml('08:00')->assertSeeInHtml('https://example.com/aula')->assertSeeInHtml('Apostila')->assertSeeInHtml('/storage/cursos/material.pdf')->assertSeeInHtml('Favor confirmar recebimento');
    $conferencia = new AvisoConferenciaTreinamentoMail($agenda);
    $conferencia->assertHasSubject('Conferência de link e materiais')->assertSeeInHtml('14/11/2026')->assertSeeInHtml('Curso exemplo');
    $aviso = new AvisoPendenciasTreinamentoMail($agenda);
    $aviso->assertSeeInHtml('15/11/2026')->assertSeeInHtml('O e-mail de materiais e link de acesso não foram enviados aos inscritos. As informações estão faltantes ou inválidas.')->assertSeeInHtml('Revise os dados e envie manualmente para cada inscrito.');
});

it('não envia após o dia previsto para cada tipo de email', function (string $tipo) {
    Mail::fake();
    Log::spy();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    if ($tipo === 'participante') {
        [$agenda, $inscrito, $id] = prepararEmailParticipante();
    } else {
        agendaEmail($tipo === 'conferencia' ? '2026-11-16' : '2026-11-15', ['link_aula' => null]);
        $this->artisan('cursos:emails-automaticos')->assertSuccessful();
        $id = Mail::queued($tipo === 'conferencia' ? AvisoConferenciaTreinamentoMail::class : AvisoPendenciasTreinamentoMail::class)->first();
    }
    $this->travelTo(Carbon::parse('2026-11-15 06:00:00', 'America/Sao_Paulo'));
    $id->send(mailerArray()->mailer('array'));
    Mail::assertNothingSent();
    Log::shouldHaveReceived('warning')->once();
    $table = $tipo === 'participante' ? 'curso_inscritos' : 'agenda_cursos';
    expect(DB::table($tipo === 'participante' ? 'curso_inscritos' : 'agenda_cursos')->whereNotNull($tipo === 'participante' ? 'informacoes_enviadas_em' : ($tipo === 'conferencia' ? 'conferencia_enviada_em' : 'pendencias_enviadas_em'))->count())->toBe(0);
})->with(['conferencia', 'participante', 'pendencias']);

it('respeita a janela de 24 horas para cada tipo de email', function (string $tipo, string $instante, bool $deveEnviar) {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));

    if ($tipo === 'participante') {
        [, , $id] = prepararEmailParticipante();
    } else {
        agendaEmail($tipo === 'conferencia' ? '2026-11-16' : '2026-11-15', ['link_aula' => null]);
        $this->artisan('cursos:emails-automaticos')->assertSuccessful();
        $id = Mail::queued($tipo === 'conferencia' ? AvisoConferenciaTreinamentoMail::class : AvisoPendenciasTreinamentoMail::class)->first();
    }

    $this->travelTo(Carbon::parse($instante, 'America/Sao_Paulo'));
    $id->send(mailerArray()->mailer('array'));
    $tabela = $tipo === 'participante' ? 'curso_inscritos' : 'agenda_cursos';
    $coluna = $tipo === 'participante' ? 'informacoes_enviadas_em' : ($tipo === 'conferencia' ? 'conferencia_enviada_em' : 'pendencias_enviadas_em');
    expect(DB::table($tabela)->whereNotNull($coluna)->count())->toBe($deveEnviar ? 1 : 0);
})->with(['conferencia', 'participante', 'pendencias'])->with([
    'antes da abertura' => ['2026-11-14 05:59:59', false],
    'abertura' => ['2026-11-14 06:00:00', true],
    'após a meia-noite' => ['2026-11-15 00:01:00', true],
    'antes do encerramento' => ['2026-11-15 05:59:59', true],
    'encerramento' => ['2026-11-15 06:00:00', false],
]);

it('exibe a data da véspera no aviso administrativo antes e após a meia-noite', function () {
    $agenda = agendaEmail('2026-11-16');
    $agenda->load('curso');

    $this->travelTo(Carbon::parse('2026-11-14 23:59:59', 'America/Sao_Paulo'));
    (new AvisoConferenciaTreinamentoMail($agenda))->assertSeeInHtml('15/11/2026, serão enviados os materiais e link do zoom para o treinamento Curso exemplo.');

    $this->travelTo(Carbon::parse('2026-11-15 00:00:00', 'America/Sao_Paulo'));
    (new AvisoConferenciaTreinamentoMail($agenda))->assertSeeInHtml('15/11/2026, serão enviados os materiais e link do zoom para o treinamento Curso exemplo.');
});

it('processa o email na fila database após a abertura da janela', function () {
    config(['mail.default' => 'array']);
    $this->travelTo(Carbon::parse('2026-11-14 05:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(DB::table('jobs')->value('available_at'))->toBe(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo')->timestamp);
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true])->assertSuccessful();
    expect($agenda->fresh()->conferencia_enviada_em)->not->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('registra falha definitiva de cada aviso administrativo', function (string $classe, string $tipo) {
    Log::spy();
    $agenda = agendaEmail('2026-11-16');
    $mail = new $classe($agenda);
    $exception = new RuntimeException('Falha de transporte');

    $mail->failed($exception);

    Log::shouldHaveReceived('error')->once()->with('Falha definitiva no envio de e-mail automático de curso.', Mockery::on(fn ($contexto) => $contexto['tipo'] === $tipo && $contexto['exception'] === $exception));
})->with([
    [AvisoConferenciaTreinamentoMail::class, 'conferencia'],
    [AvisoPendenciasTreinamentoMail::class, 'pendencias'],
]);

it('a action de inscritos encaminha as pendências ao mailable específico', function () {
    Mail::fake();
    $agenda = agendaEmail('2026-11-15', ['link_aula' => null]);
    $inscrito = $agenda->inscritos()->create(['pessoa_id' => PessoaFactory::new()->create()->id, 'nome' => 'Ana', 'email' => 'ana@example.com', 'data_inscricao' => now()]);

    app(EnfileirarEmailInscritoCursoAction::class)->execute($agenda, '2026-11-15', $inscrito->id);

    Mail::assertQueued(AvisoPendenciasTreinamentoMail::class, fn ($mail) => $mail->hasTo('administrativo@redemetrologica.com.br'));
    Mail::assertNotQueued(InformacoesTreinamentoMail::class);
    Mail::assertNotQueued(AvisoConferenciaTreinamentoMail::class);
});

it('cada aviso administrativo respeita seu atraso e confirma somente seu próprio envio', function (string $classe, string $data, string $coluna) {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail($data);
    $mail = (new $classe($agenda))->to('administrativo@redemetrologica.com.br');
    expect($mail->delay->toDateTimeString())->toBe('2026-11-14 06:00:00');

    expect($mail->send(mailerArray()->mailer('array')))->not->toBeNull();
    expect($mail->send(mailerArray()->mailer('array')))->toBeNull();
    expect($agenda->fresh()->$coluna)->not->toBeNull();
    $outraColuna = $coluna === 'conferencia_enviada_em' ? 'pendencias_enviadas_em' : 'conferencia_enviada_em';
    expect($agenda->fresh()->$outraColuna)->toBeNull();
})->with([
    [AvisoConferenciaTreinamentoMail::class, '2026-11-16', 'conferencia_enviada_em'],
    [AvisoPendenciasTreinamentoMail::class, '2026-11-15', 'pendencias_enviadas_em'],
]);
