<?php

use App\Actions\Agendamentos\ExecutarRodadaAgendada;
use App\Jobs\EnviarEmailAgendadoJob;
use App\Support\ProcessosAgendados;
use Carbon\CarbonImmutable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;

it('executa as etapas uma vez em sequência enquanto os e-mails aguardam na fila', function () {
    $dia = CarbonImmutable::parse('2026-11-23 00:00', 'America/Sao_Paulo');
    $this->travelTo($dia);
    $action = app(ExecutarRodadaAgendada::class);
    $etapas = [];

    $resultado = $action->execute($dia, function (string $comando) use (&$etapas, $action, $dia): int {
        $etapas[] = $comando;

        if ($comando === 'certificados:enviar-pendente') {
            $action->execute($dia, static fn () => throw new LogicException('A rodada já foi iniciada.'));
            foreach (range(1, 100) as $ordem) {
                $email = (new Mailable)->subject("Certificado {$ordem}")->html('<p>Teste</p>');
                EnviarEmailAgendadoJob::dispatch($email, 'teste@example.test');
            }
        }

        if ($comando === 'interlab:enviar-certificado-pendente') {
            expect(DB::table('jobs')->where('queue', ProcessosAgendados::FILA_EMAIL)->count())->toBe(100);

            return 1;
        }

        return 0;
    });

    expect($resultado)->toBe(1)
        ->and($etapas)->toBe([
            'certificados:enviar-pendente',
            'interlab:enviar-certificado-pendente',
            'app:processar-senhas-interlab-confirmado',
            'avaliacoes:enviar-lembrete-proc-laboratorio',
            'avaliacoes:enviar-lembrete-proposta-acoes',
            'avaliacoes:enviar-lembrete-acoes-corretivas',
            'cursos:emails-automaticos',
        ])
        ->and(DB::table('rodadas_agendadas')->value('status'))->toBe('concluida_com_falhas');
});

it('retoma somente as etapas que ainda não começaram', function () {
    $dia = CarbonImmutable::parse('2026-11-23 00:00', 'America/Sao_Paulo');
    $this->travelTo($dia);
    $action = app(ExecutarRodadaAgendada::class);
    $action->execute($dia, static fn () => 0);

    $rodada = DB::table('rodadas_agendadas')->first();
    $etapas = json_decode($rodada->etapas, true, 512, JSON_THROW_ON_ERROR);
    $etapas['interlab:enviar-certificado-pendente']['status'] = 'em_execucao';
    $etapas['app:processar-senhas-interlab-confirmado']['status'] = 'pendente';
    DB::table('rodadas_agendadas')->where('id', $rodada->id)->update([
        'status' => 'em_execucao',
        'etapas' => json_encode($etapas, JSON_THROW_ON_ERROR),
    ]);

    $executadas = [];
    $resultado = $action->execute($dia, function (string $comando) use (&$executadas): int {
        $executadas[] = $comando;

        return 0;
    }, retomar: true);

    $etapas = json_decode(DB::table('rodadas_agendadas')->value('etapas'), true, 512, JSON_THROW_ON_ERROR);
    expect($resultado)->toBe(1)
        ->and($executadas)->toBe(['app:processar-senhas-interlab-confirmado'])
        ->and($etapas['interlab:enviar-certificado-pendente']['status'])->toBe('interrompida');
});

it('monta a rodada e o rastreio independente com opções válidas para o CLI', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('agendamentos:executar-rodada --verificar ')
        ->expectsOutputToContain('interlab:rastrear-itens-postagem --agendado ')
        ->doesntExpectOutputToContain('--verificar=')
        ->doesntExpectOutputToContain('--agendado=')
        ->assertSuccessful();
});

it('enfileira uma vez os avisos de ausência de certificados ao executar a rodada real', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-23 00:00', 'America/Sao_Paulo'));

    $this->artisan('agendamentos:executar-rodada')->assertSuccessful();
    $this->artisan('agendamentos:executar-rodada')->assertSuccessful();

    $rodada = DB::table('rodadas_agendadas')->sole();
    $jobs = DB::table('jobs')->where('queue', ProcessosAgendados::FILA_EMAIL)->orderBy('id')->get();
    $contextos = $jobs->map(fn ($job) => json_decode($job->payload, true, 512, JSON_THROW_ON_ERROR)['processo_agendado']);

    expect($rodada->status)->toBe('concluida')
        ->and($contextos->pluck('etapa')->all())->toBe(['certificados:enviar-pendente', 'interlab:enviar-certificado-pendente'])
        ->and($contextos->pluck('rodada_id')->unique()->all())->toBe([$rodada->id]);
});
