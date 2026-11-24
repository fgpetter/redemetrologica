<?php

namespace App\Actions\Agendamentos;

use App\Support\ProcessosAgendados;
use Carbon\CarbonImmutable;
use Symfony\Component\Console\Command\Command;
use Throwable;

class ExecutarRodadaAgendada
{
    private const ETAPAS = [
        'certificados:enviar-pendente',
        'interlab:enviar-certificado-pendente',
        'app:processar-senhas-interlab-confirmado',
        'avaliacoes:enviar-lembrete-proc-laboratorio',
        'avaliacoes:enviar-lembrete-proposta-acoes',
        'avaliacoes:enviar-lembrete-acoes-corretivas',
        'cursos:emails-automaticos',
    ];

    /** @param callable(string): int $executarComando */
    public function execute(CarbonImmutable $inicio, callable $executarComando): int
    {
        return ProcessosAgendados::rodada($inicio, function () use ($executarComando): int {
            $falhas = 0;

            foreach (self::ETAPAS as $comando) {
                try {
                    $falhas += (int) ($executarComando($comando) !== Command::SUCCESS);
                } catch (Throwable $exception) {
                    $falhas++;

                    ProcessosAgendados::log('rodada.erro_comando', [
                        'comando' => $comando,
                        'exception_class' => $exception::class,
                    ], 'error');
                }
            }

            ProcessosAgendados::resumo(['comandos_com_falha' => $falhas]);

            return $falhas > 0 ? Command::FAILURE : Command::SUCCESS;
        });
    }
}
