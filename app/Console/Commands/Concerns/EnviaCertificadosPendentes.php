<?php

namespace App\Console\Commands\Concerns;

use App\Mail\AvisoSemCertificadosPendentesMail;
use App\Support\ProcessosAgendados;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

trait EnviaCertificadosPendentes
{
    /** @param callable(int): bool $enfileirar */
    protected function enviarCertificadosPendentes(
        Builder $inscritos,
        callable $enfileirar,
        AvisoSemCertificadosPendentesMail $aviso,
    ): int {
        $enfileirados = 0;
        $ignorados = 0;
        $falhas = 0;
        $cursor = null;
        $candidatos = $inscritos->toBase()->orderBy('data_inscricao')->orderBy('id');

        do {
            $pagina = (clone $candidatos)->cursorPaginate(100, ['id', 'data_inscricao'], 'cursor', $cursor);

            foreach ($pagina as $inscrito) {
                try {
                    if ($enfileirar($inscrito->id)) {
                        $enfileirados++;
                    } else {
                        $ignorados++;
                    }
                } catch (ValidationException) {
                    $ignorados++;
                } catch (Throwable $exception) {
                    $falhas++;
                    ProcessosAgendados::log('certificado_falha_enfileiramento', [
                        'inscrito_id' => $inscrito->id,
                        'exception_class' => $exception::class,
                    ], 'error');
                }
            }

            $cursor = $pagina->nextCursor();
        } while ($cursor !== null);

        $avisarAusencia = $enfileirados === 0 && $falhas === 0;
        ProcessosAgendados::resumo([
            'certificados_enfileirados' => $enfileirados,
            'ignorados' => $ignorados,
            'falhas' => $falhas,
        ]);

        if ($avisarAusencia) {
            $destinatario = 'sistema@redemetrologica.com.br';

            if (ProcessosAgendados::emRodada()) {
                $aviso->onConnection('database');
                ProcessosAgendados::enfileirar(fn ($horario) => Mail::to($destinatario)->later($horario, $aviso));
            } else {
                Mail::raw($aviso->mensagem, fn ($message) => $message->to($destinatario)->subject($aviso->envelope()->subject));
            }

            ProcessosAgendados::resumo(['avisos_sem_pendencias' => 1]);
            $this->info('Nenhuma inscrição apta para envio.');
        }

        $this->info("Enfileirados {$enfileirados} certificado(s). Ignorados {$ignorados}. Falhas {$falhas}.");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}
