<?php

use App\Actions\EnfileirarFr98Action;
use App\Exceptions\Handler;
use App\Helpers\ValorPorExtenso;
use App\Jobs\EnviarFr98Job;
use App\Mail\ExceptionOccured;
use App\Mail\Fr98Mail;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\MaterialPadrao;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\PessoaFactory;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    config([
        'logging.channels.validation' => config('logging.channels.null'),
        'logging.channels.mailsent' => config('logging.channels.null'),
    ]);
    Log::forgetChannel('validation');
    Log::forgetChannel('mailsent');
    $this->travelTo(Carbon::parse('2026-11-17 10:00:00', 'America/Sao_Paulo'));
    $this->userFr98 = User::factory()->create();
    $permission = DB::table('permissions')->insertGetId(['permission' => 'funcionario']);
    DB::table('permission_user')->insert(['permission_id' => $permission, 'user_id' => $this->userFr98->id]);
    $this->actingAs($this->userFr98);
});

function agendaFr98(array $extra = []): AgendaCursos
{
    $curso = Curso::create(['descricao' => 'Gestão de Riscos', 'tipo_curso' => 'OFICIAL']);
    $pessoa = PessoaFactory::new()->create(['nome_razao' => 'Instrutor FR98', 'email' => 'instrutor@example.com']);
    $instrutor = Instrutor::create(['pessoa_id' => $pessoa->id, 'situacao' => true]);
    $agenda = AgendaCursos::create(array_merge([
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'status' => 'CONFIRMADO',
        'tipo_agendamento' => 'ONLINE',
        'data_inicio' => '2026-11-20',
        'horario' => '08h30 às 17h30',
        'link_aula' => 'https://example.com/reuniao-original',
    ], $extra));
    $agenda->inscritos()->create([
        'pessoa_id' => PessoaFactory::new()->create()->id,
        'nome' => 'Participante FR98',
        'email' => 'participante@example.com',
        'data_inscricao' => now(),
    ]);
    despesaFr98($agenda, 'HORAS-AULA', 8, 140);

    return $agenda;
}

function despesaFr98(AgendaCursos $agenda, string $descricao, float $quantidade, float $valor): void
{
    $material = MaterialPadrao::create(['descricao' => $descricao, 'tipo' => 'CURSOS']);
    $agenda->despesas()->create([
        'material_padrao_id' => $material->id,
        'quantidade' => $quantidade,
        'valor' => $valor,
        'total' => 0, // FR98 deve calcular, sem confiar neste total.
    ]);
}

function jobFr98(): EnviarFr98Job
{
    $payload = json_decode(DB::table('jobs')->orderByDesc('id')->value('payload'), true);

    return unserialize($payload['data']['command']);
}

it('enfileira nas duas modalidades com feedback SWAL e envia o snapshot uma única vez', function (string $tipo) {
    Mail::fake();
    $agenda = agendaFr98(['tipo_agendamento' => $tipo, 'status' => $tipo === 'IN-COMPANY' ? 'CANCELADO' : 'CONFIRMADO']);
    $origem = route($tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-insert' : 'agendamento-curso-insert', $agenda->uid);

    $this->from($origem)->post(route('agendamento-curso-enviar-fr98', $agenda->uid))
        ->assertRedirect($origem)->assertSessionHas('success');
    expect($agenda->fresh()->fr98_envio_id)->not->toBeNull()
        ->and($agenda->fresh()->fr98_enviado_em)->toBeNull();
    Mail::assertNothingSent();
    $job = jobFr98();
    expect($job->connection)->toBe('database')->and($job->queue)->toBe('default');
    $agenda->update(['link_aula' => 'https://example.com/alterada', 'horario' => '19h']);
    $agenda->instrutor->pessoa->update(['email' => 'novo@example.com']);
    $this->travel(5)->minutes();
    $job->handle();
    $job->handle();

    Mail::assertSentCount(1);
    Mail::assertSent(Fr98Mail::class, function ($mail) {
        return $mail->hasTo('instrutor@example.com')
            && $mail->hasFrom('eventos@redemetrologica.com.br')
            && $mail->hasCc('eventos@redemetrologica.com.br')
            && $mail->hasReplyTo('eventos@redemetrologica.com.br')
            && $mail->dados['link'] === 'https://example.com/reuniao-original'
            && $mail->dados['horario'] === '08h30 às 17h30';
    });
    expect($agenda->fresh()->fr98_envio_id)->toBeNull()
        ->and($agenda->fresh()->fr98_enviado_em->format('H:i'))->toBe('10:05');
    $alert = (string) $this->blade('<x-alerts.alert />');
    expect($alert)->toContain('Swal.mixin', 'FR98 enfileirado');
    $this->get($origem)->assertSuccessful()
        ->assertSee('Enviado em 17/11/2026 10:05')->assertSee('Reenviar FR98')
        ->assertDontSee('Envio pendente');
})->with(['ONLINE', 'IN-COMPANY']);

it('reporta falhas técnicas ao Handler e exibe a mensagem padrão nas duas modalidades', function (string $tipo, string $falha) {
    Mail::fake();
    expect(app(ExceptionHandler::class))->toBeInstanceOf(Handler::class);
    $agenda = agendaFr98(['tipo_agendamento' => $tipo]);
    $exception = new RuntimeException('Detalhe interno confidencial');
    if ($falha === 'migration mysql') {
        $previous = new PDOException("Unknown column 'fr98_envio_id' in 'field list'");
        $previous->errorInfo = ['42S22', 1054, $previous->getMessage()];
        $exception = new QueryException('mysql', 'update agenda_cursos set fr98_envio_id = ?', ['interno'], $previous);
    }
    $this->mock(EnfileirarFr98Action::class, function ($mock) use ($exception) {
        $mock->shouldReceive('execute')->once()->andThrow($exception);
    });
    $prefixo = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-' : 'agendamento-curso-';
    $this->post(route($prefixo.'update', $agenda->uid), [
        'curso_id' => $agenda->curso_id,
        'instrutor_id' => $agenda->instrutor_id,
        'tipo_agendamento' => $tipo,
        'status' => 'CONFIRMADO',
        'status_proposta' => 'PENDENTE',
        'data_inicio' => '2026-11-25',
        'horario' => '14h às 18h',
        'link_aula' => 'https://example.com/nova',
        'enviar_fr98' => '1',
    ])->assertRedirect(route($prefixo.'insert', $agenda->uid))
        ->assertSessionHas('error', 'Não foi possível enviar, erro interno no sistema');
    Mail::assertQueued(ExceptionOccured::class);

    expect($this->blade('<x-alerts.alert />')->__toString())->toContain('Swal.mixin');
    expect($agenda->fresh()->horario)->toBe('14h às 18h');
    $this->assertDatabaseCount('jobs', 0);
})->with(['ONLINE', 'IN-COMPANY'])->with(['migration mysql', 'inesperada']);

it('bloqueia cada pré-requisito ausente com feedback e sem criar job', function (string $pendencia) {
    $agenda = agendaFr98();
    if ($pendencia === 'instrutor') {
        DB::statement('PRAGMA defer_foreign_keys = ON');
    }
    match ($pendencia) {
        'status' => $agenda->update(['status' => 'AGENDADO']),
        'instrutor' => $agenda->update(['instrutor_id' => 999999]),
        'email ausente' => $agenda->instrutor->pessoa->update(['email' => null]),
        'email invalido' => $agenda->instrutor->pessoa->update(['email' => 'invalido']),
        'despesa' => $agenda->despesas()->delete(),
        'quantidade' => $agenda->despesas()->update(['quantidade' => 0]),
        'valor' => $agenda->despesas()->update(['valor' => 0]),
        'link' => $agenda->update(['link_aula' => null]),
        'data' => $agenda->update(['data_inicio' => null]),
        'horario' => $agenda->update(['horario' => '  ']),
        'participantes' => $agenda->inscritos()->delete(),
    };
    $this->post(route('agendamento-curso-enviar-fr98', $agenda->uid))->assertSessionHas('error');
    $this->assertDatabaseCount('jobs', 0);
    expect($agenda->fresh()->fr98_envio_id)->toBeNull();
    expect((string) $this->blade('<x-alerts.alert />'))->toContain('Swal.mixin');
})->with(['status', 'instrutor', 'email ausente', 'email invalido', 'despesa', 'quantidade', 'valor', 'link', 'data', 'horario', 'participantes']);

it('permite FR98 In Company sem participantes e informa zero no e-mail', function () {
    Mail::fake();
    $agenda = agendaFr98(['tipo_agendamento' => 'IN-COMPANY', 'status' => 'AGENDADO']);
    $agenda->inscritos()->delete();

    $this->post(route('agendamento-curso-enviar-fr98', $agenda->uid))
        ->assertSessionHas('success');

    $job = jobFr98();
    expect($job->snapshot['participantes'])->toBe(0);
    $job->handle();

    Mail::assertSent(Fr98Mail::class, fn ($mail) => $mail->dados['participantes'] === 0);
    expect($agenda->fresh()->fr98_enviado_em)->not->toBeNull();
});

it('lista todas as pendências e registra somente nomes dos campos', function () {
    $agenda = agendaFr98(['status' => 'AGENDADO', 'link_aula' => null, 'horario' => null]);
    $logger = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->with('validation')->once()->andReturn($logger);
    $logger->shouldReceive('warning')->once()->withArgs(function ($message, $context) use ($agenda) {
        return $context === ['agenda_curso_id' => $agenda->id, 'campos' => ['status', 'link_aula', 'horario']];
    });
    $this->post(route('agendamento-curso-enviar-fr98', $agenda->uid))
        ->assertSessionHas('error', fn ($message) => str_contains($message, 'CONFIRMADA') && str_contains($message, 'link') && str_contains($message, 'horário'));
});

it('impede pedidos duplicados e refaz dados no reenvio preservando a data anterior', function () {
    Mail::fake();
    $agenda = agendaFr98();
    $url = route('agendamento-curso-enviar-fr98', $agenda->uid);
    $this->post($url)->assertSessionHas('success');
    $primeiro = jobFr98();
    $this->post($url)->assertSessionHas('error', 'Já existe um envio de FR98 pendente.');
    $this->assertDatabaseCount('jobs', 1);
    $primeiro->handle();
    $dataAnterior = $agenda->fresh()->fr98_enviado_em;
    $agenda->update(['horario' => '14h']);
    $this->travel(1)->hour();
    $this->post($url)->assertSessionHas('success');
    $segundo = jobFr98();
    expect($segundo->snapshot['horario'])->toBe('14h')
        ->and($segundo->tentativaId)->not->toBe($primeiro->tentativaId)
        ->and($agenda->fresh()->fr98_enviado_em->equalTo($dataAnterior))->toBeTrue();
    $primeiro->failed(new RuntimeException('Tentativa antiga'));
    expect($agenda->fresh()->fr98_envio_id)->toBe($segundo->tentativaId);
    $segundo->handle();
    expect($agenda->fresh()->fr98_enviado_em->greaterThan($dataAnterior))->toBeTrue();
});

it('processa pelo worker e libera a reserva após falha definitiva sem apagar o último sucesso', function () {
    $agenda = agendaFr98(['fr98_enviado_em' => now()->subDay()]);
    $dataAnterior = $agenda->fr98_enviado_em;
    app(EnfileirarFr98Action::class)->execute($agenda->id);
    Mail::shouldReceive('to')->with('instrutor@example.com')->times(3)->andThrow(new RuntimeException('SMTP indisponível'));
    config(['logging.channels.single' => config('logging.channels.null')]);

    for ($tentativa = 1; $tentativa <= 3; $tentativa++) {
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--sleep' => 0])->assertSuccessful();
        if ($tentativa < 3) {
            expect($agenda->fresh()->fr98_envio_id)->not->toBeNull();
            $this->travel(60)->seconds();
        }
    }
    $this->assertDatabaseCount('failed_jobs', 1);
    expect($agenda->fresh()->fr98_envio_id)->toBeNull()
        ->and($agenda->fresh()->fr98_enviado_em->equalTo($dataAnterior))->toBeTrue();
});

it('calcula o conteúdo do FR98 com deslocamento, período e contatos In Company sem tabelas', function () {
    $empresa = PessoaFactory::new()->create(['nome_razao' => 'Empresa FR98']);
    $agenda = agendaFr98([
        'tipo_agendamento' => 'IN-COMPANY', 'empresa_id' => $empresa->id,
        'endereco_local' => 'Rua Teste, 123', 'contato' => 'Maria', 'contato_telefone' => '51999999999',
        'data_fim' => '2026-11-21',
    ]);
    despesaFr98($agenda, 'DESLOCAMENTOS INSTRUTOR', 2, 50.25);
    app(EnfileirarFr98Action::class)->execute($agenda->id);
    $dados = jobFr98()->snapshot;
    expect($dados['bonus'])->toBe('100,50')->and($dados['total'])->toBe('1.220,50')
        ->and($dados['total_extenso'])->toBe('mil duzentos e vinte reais e cinquenta centavos')
        ->and($dados['local'])->toContain('EMPRESA FR98', 'Rua Teste, 123')
        ->and($dados['contato'])->toBe('Maria')
        ->and($dados['data'])->toBe('20/11/2026 a 21/11/2026');
    $mail = new Fr98Mail($dados);
    $mail->assertHasSubject('FR98 — Instruções para o instrutor — Gestão de Riscos')
        ->assertSeeInHtml('ORIENTAÇÕES:')->assertSeeInHtml('POR FAVOR GRAVAR O TREINAMENTO')
        ->assertSeeInHtml('https://example.com/reuniao-original')->assertSeeInHtml('Raquel Bresolin')
        ->assertSeeInHtml('alt="Rede Metrológica RS"', false)
        ->assertSeeInHtml('© 2026 Sistema Rede Metrológica RS.');
    expect($mail->render())->not->toContain('<table')->and($mail->attachments())->toBeEmpty();
});

it('usa bônus zero e data simples e entrega pelo transporte array', function () {
    config(['mail.default' => 'array']);
    $agenda = agendaFr98(['data_fim' => '2026-11-20']);
    app(EnfileirarFr98Action::class)->execute($agenda->id);
    $dados = jobFr98()->snapshot;
    expect($dados['bonus'])->toBe('0,00')->and($dados['total'])->toBe('1.120,00')
        ->and($dados['total_extenso'])->toBe('mil cento e vinte reais')
        ->and($dados['data'])->toBe('20/11/2026')
        ->and($dados['local'])->toBe('Rede Metrológica RS')
        ->and($dados['contato'])->toBe('—');
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true])->assertSuccessful();
    expect($agenda->fresh()->fr98_enviado_em)->not->toBeNull();
    $this->assertDatabaseCount('jobs', 0);
});

it('escreve reais e centavos com concordância', function (int $centavos, string $esperado) {
    expect(ValorPorExtenso::reais($centavos))->toBe($esperado);
})->with([[100, 'um real'], [101, 'um real e um centavo'], [1, 'um centavo'], [250, 'dois reais e cinquenta centavos'], [0, 'zero reais']]);

it('restringe envio a usuários autorizados e usa UID público', function () {
    $agenda = agendaFr98();
    $this->actingAs(User::factory()->create())
        ->post(route('agendamento-curso-enviar-fr98', $agenda->uid))->assertNotFound();
    $this->actingAs($this->userFr98)
        ->post(route('agendamento-curso-enviar-fr98', $agenda->id))->assertNotFound();
    $this->assertDatabaseCount('jobs', 0);
});

it('renderiza o controle no formulário principal nas duas telas e estados de envio', function (string $tipo) {
    $agenda = agendaFr98(['tipo_agendamento' => $tipo]);
    $route = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-insert' : 'agendamento-curso-insert';
    $response = $this->get(route($route, $agenda->uid))->assertSuccessful()->assertSee('Enviar FR98')->assertDontSee('form-envio-fr98');
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);
    $formBotao = $xpath->query('//button[@name="enviar_fr98"]/ancestor::form')->item(0);
    $formInstrutor = $xpath->query('//select[@name="instrutor_id"]/ancestor::form')->item(0);
    expect($formBotao)->not->toBeNull()
        ->and($formBotao->isSameNode($formInstrutor))->toBeTrue();
    $agenda->update(['fr98_envio_id' => 'pendente']);
    $this->get(route($route, $agenda->uid))->assertSuccessful()
        ->assertSee('Enviar FR98')->assertDontSee('Envio pendente')->assertSee('disabled');
    $agenda->update(['fr98_enviado_em' => now()]);
    $this->get(route($route, $agenda->uid))->assertSuccessful()->assertSee('Reenviar FR98')->assertSee('Enviado em 17/11/2026 10:00');
    $agenda->update(['fr98_envio_id' => 'pendente']);
    $this->get(route($route, $agenda->uid))->assertSuccessful()
        ->assertSee('Reenviar FR98')->assertSee('Enviado em 17/11/2026 10:00')
        ->assertDontSee('Envio pendente')->assertSee('disabled');
})->with(['ONLINE', 'IN-COMPANY']);

it('salva os campos preenchidos antes de solicitar FR98 nas duas modalidades', function (string $tipo) {
    $agenda = agendaFr98(['tipo_agendamento' => $tipo, 'link_aula' => null, 'horario' => null]);
    $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create(['email' => 'instrutor-novo@example.com'])->id, 'situacao' => true]);
    $route = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-update' : 'agendamento-curso-update';
    $edit = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-insert' : 'agendamento-curso-insert';
    $this->post(route($route, $agenda->uid), [
        'curso_id' => $agenda->curso_id,
        'instrutor_id' => $instrutor->id,
        'tipo_agendamento' => $tipo,
        'status' => 'CONFIRMADO',
        'status_proposta' => 'PENDENTE',
        'data_inicio' => '2026-11-25',
        'horario' => '14h às 18h',
        'link_aula' => 'https://example.com/link-preenchido',
        'enviar_fr98' => '1',
    ])->assertRedirect(route($edit, $agenda->uid))->assertSessionHas('success');

    expect($agenda->fresh()->horario)->toBe('14h às 18h');
    $this->assertDatabaseCount('jobs', 1);
    $job = jobFr98();
    expect($job->snapshot['horario'])->toBe('14h às 18h')
        ->and($job->snapshot['link'])->toBe('https://example.com/link-preenchido')
        ->and($job->snapshot['data'])->toBe('25/11/2026')
        ->and($job->snapshot['destinatario'])->toBe('instrutor-novo@example.com');
})->with(['ONLINE', 'IN-COMPANY']);

it('retorna à edição após cadastrar ou atualizar uma agenda', function (string $tipo, string $operacao) {
    $agenda = agendaFr98(['tipo_agendamento' => $tipo]);
    $prefixo = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-' : 'agendamento-curso-';
    $response = $this->post(route($prefixo.$operacao, $operacao === 'update' ? $agenda->uid : []), [
        'curso_id' => $agenda->curso_id,
        'instrutor_id' => $agenda->instrutor_id,
        'tipo_agendamento' => $tipo,
        'status' => 'CONFIRMADO',
        'status_proposta' => 'PENDENTE',
        'data_inicio' => '2026-11-25',
    ]);
    $salva = $operacao === 'create' ? AgendaCursos::latest('id')->first() : $agenda;
    $response->assertRedirect(route($prefixo.'insert', $salva->uid))->assertSessionHas('success');
})->with(['ONLINE', 'IN-COMPANY'])->with(['create', 'update']);

it('preserva os dados salvos quando faltar requisito do FR98', function (string $tipo) {
    $agenda = agendaFr98(['tipo_agendamento' => $tipo]);
    $agenda->despesas()->delete();
    $prefixo = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-' : 'agendamento-curso-';
    $this->post(route($prefixo.'update', $agenda->uid), [
        'curso_id' => $agenda->curso_id,
        'instrutor_id' => $agenda->instrutor_id,
        'tipo_agendamento' => $tipo,
        'status' => 'CONFIRMADO',
        'status_proposta' => 'PENDENTE',
        'data_inicio' => '2026-11-25',
        'horario' => 'Novo horário',
        'link_aula' => 'https://example.com/nova',
        'enviar_fr98' => '1',
    ])->assertRedirect(route($prefixo.'insert', $agenda->uid))
        ->assertSessionHas('error', fn ($message) => str_contains($message, 'HORAS-AULA'));

    expect($agenda->fresh()->horario)->toBe('Novo horário');
    $this->assertDatabaseCount('jobs', 0);
})->with(['ONLINE', 'IN-COMPANY']);

it('bloqueia salvamento e envio quando o formulário principal é inválido', function (string $tipo) {
    $agenda = agendaFr98(['tipo_agendamento' => $tipo]);
    $prefixo = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-' : 'agendamento-curso-';
    $origem = route($prefixo.'insert', $agenda->uid);
    $this->from($origem)->post(route($prefixo.'update', $agenda->uid), [
        'curso_id' => $agenda->curso_id,
        'instrutor_id' => $agenda->instrutor_id,
        'tipo_agendamento' => $tipo,
        'status' => 'CONFIRMADO',
        'status_proposta' => 'PENDENTE',
        'data_inicio' => '2026-11-25',
        'horario' => 'Não deve ser salvo',
        'link_aula' => 'link-invalido',
        'enviar_fr98' => '1',
    ])->assertRedirect($origem)->assertSessionHasErrors('link_aula')->assertSessionHas('error');

    expect($agenda->fresh()->horario)->toBe('08h30 às 17h30');
    $this->assertDatabaseCount('jobs', 0);
})->with(['ONLINE', 'IN-COMPANY']);

it('exibe erro padrão quando a migration do FR98 está ausente', function (string $tipo) {
    $agenda = agendaFr98(['tipo_agendamento' => $tipo]);
    Schema::table('agenda_cursos', fn ($table) => $table->dropColumn(['fr98_envio_id', 'fr98_enviado_em']));
    $prefixo = $tipo === 'IN-COMPANY' ? 'agendamento-curso-in-company-' : 'agendamento-curso-';
    $this->post(route($prefixo.'update', $agenda->uid), [
        'curso_id' => $agenda->curso_id,
        'instrutor_id' => $agenda->instrutor_id,
        'tipo_agendamento' => $tipo,
        'status' => 'CONFIRMADO',
        'status_proposta' => 'PENDENTE',
        'data_inicio' => '2026-11-25',
        'horario' => '14h às 18h',
        'link_aula' => 'https://example.com/nova',
        'enviar_fr98' => '1',
    ])->assertRedirect(route($prefixo.'insert', $agenda->uid))
        ->assertSessionHas('error', 'Não foi possível enviar, erro interno no sistema');

    expect($agenda->fresh()->horario)->toBe('14h às 18h');
    $this->assertDatabaseCount('jobs', 0);
})->with(['ONLINE', 'IN-COMPANY']);
