<?php

use App\Actions\Agendamentos\ExecutarRodadaAgendada;
use App\Mail\AvisoSemCertificadosPendentesMail;
use App\Support\ProcessosAgendados;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Monolog\Handler\TestHandler;

function enfileirarAvisoDaRodada(): void
{
    $email = (new AvisoSemCertificadosPendentesMail('Teste', 'Mensagem de teste.'))
        ->onConnection('database');

    ProcessosAgendados::enfileirar(fn ($horario) => Mail::to('teste@example.test')->later($horario, $email));
}

it('mantém os horários entre comandos, ignora rollback e segue após falha parcial', function () {
    $inicio = CarbonImmutable::parse('2026-11-23 00:00', 'America/Sao_Paulo');
    $this->travelTo($inicio);
    $registros = new TestHandler;
    Log::channel('processos_agendados')->getLogger()->setHandlers([$registros]);
    $executados = [];

    $resultado = app(ExecutarRodadaAgendada::class)->execute($inicio, function (string $comando) use (&$executados): int {
        $executados[] = $comando;

        return ProcessosAgendados::execucao($comando, function () use ($comando): int {
            if ($comando === 'certificados:enviar-pendente') {
                foreach (range(1, 5) as $ordem) {
                    DB::transaction(fn () => enfileirarAvisoDaRodada());
                }

                DB::transaction(function (): void {
                    enfileirarAvisoDaRodada();

                    throw new RuntimeException('Reverte o sexto despacho.');
                });
            }

            if ($comando === 'app:processar-senhas-interlab-confirmado') {
                foreach (range(1, 5) as $ordem) {
                    enfileirarAvisoDaRodada();
                }
            }

            return 0;
        });
    });

    expect($resultado)->toBe(1)
        ->and($executados)->toBe([
            'certificados:enviar-pendente',
            'interlab:enviar-certificado-pendente',
            'app:processar-senhas-interlab-confirmado',
            'avaliacoes:enviar-lembrete-proc-laboratorio',
            'avaliacoes:enviar-lembrete-proposta-acoes',
            'avaliacoes:enviar-lembrete-acoes-corretivas',
            'cursos:emails-automaticos',
        ])
        ->and(DB::table('jobs')->orderBy('id')->pluck('available_at')->all())
        ->toBe(array_map(fn (int $segundos): int => $inicio->getTimestamp() + $segundos, range(0, 45, 5)));

    $finais = collect($registros->getRecords())->filter(fn ($registro) => in_array($registro->message, ['processo.falha', 'processo.concluido'], true));

    expect($finais->pluck('context.emails_enfileirados')->values()->all())->toBe([5, 0, 5, 0, 0, 0, 0, 10])
        ->and($finais->first()->context['exception'])->toBe(RuntimeException::class)
        ->and($finais->last()->context['comandos_com_falha'])->toBe(1)
        ->and($finais->last()->context['ultimo_disponivel_em'])->toBe($inicio->getTimestamp() + 45);
});

it('usa a hora atual quando o despacho ultrapassa o próximo horário previsto', function () {
    $inicio = CarbonImmutable::parse('2026-11-23 00:00', 'America/Sao_Paulo');
    $this->travelTo($inicio);

    ProcessosAgendados::rodada($inicio, function (): int {
        enfileirarAvisoDaRodada();
        $this->travel(40)->seconds();
        enfileirarAvisoDaRodada();
        enfileirarAvisoDaRodada();

        return 0;
    });

    expect(DB::table('jobs')->orderBy('id')->pluck('available_at')->all())
        ->toBe(array_map(fn (int $segundos): int => $inicio->getTimestamp() + $segundos, [0, 40, 45]));
});

it('agenda a rodada à meia-noite e conserva o rastreio independente', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('agendamentos:executar-rodada')
        ->expectsOutputToContain('interlab:rastrear-itens-postagem --agendado ')
        ->doesntExpectOutputToContain('--verificar')
        ->assertSuccessful();
});

it('enfileira os avisos nativos de ausência de certificados com a mesma rodada', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-23 00:00', 'America/Sao_Paulo'));

    $this->artisan('agendamentos:executar-rodada')->assertSuccessful();

    $jobs = DB::table('jobs')->where('queue', 'default')->orderBy('id')->get();
    $payloads = $jobs->map(fn ($job) => json_decode($job->payload, true, 512, JSON_THROW_ON_ERROR));

    expect($payloads->pluck('displayName')->all())->toBe(array_fill(0, 2, AvisoSemCertificadosPendentesMail::class))
        ->and($jobs[1]->available_at - $jobs[0]->available_at)->toBe(5)
        ->and($payloads->pluck('illuminate:log:context')->unique()->count())->toBe(2);
});
