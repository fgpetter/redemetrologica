<?php

namespace App\Console\Commands;

use App\Actions\EnviarCertificadoAction;
use App\Models\CursoInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EnviarCertificadoPendenteCommand extends Command
{
    public const SEM_CERTIFICADO_PENDENTE_CACHE_KEY = 'certificados:sem-pendencia';

    protected $signature = 'certificados:enviar-pendente';

    protected $description = 'Envia certificado para o inscrito em curso mais antigo com pagamento efetivado e sem certificado emitido';

    public function handle(EnviarCertificadoAction $action): void
    {
        $resumo = new ResumoExecucao('EnviarCertificadoPendenteCommand', [], 'stack');
        $resumo->executar(function (ResumoExecucao $resumo) use ($action): void {

            if (Cache::has(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY)) {
                $resumo->resultado('ignorado', 'cache_sem_pendencias');

                return;
            }

            $inscritos = CursoInscrito::certificadosPendentes()
                ->orderBy('data_inscricao')->orderBy('id')->lazy(100);

            $contatosInvalidos = 0;

            foreach ($inscritos as $inscrito) {
                $item = $resumo->item('EnviarCertificadoAction', [
                    'tipo' => 'certificado_curso',
                    'agenda_id' => $inscrito->agenda_curso_id,
                    'inscrito_id' => $inscrito->id,
                    'inscrito_uid' => $inscrito->uid,
                ]);

                try {
                    if (Context::scope(fn (): bool => $action->executePendente($inscrito->id, resumo: $item), $item->correlacao())) {
                        $resumo->incrementar('enfileirados');
                        if ($contatosInvalidos === 0) {
                            $resumo->resultado('enfileirado');
                        }
                        $this->info("Certificado do curso enfileirado para o inscrito {$inscrito->id}.");

                        return;
                    }
                } catch (ValidationException) {
                    $contatosInvalidos++;
                    $resumo->incrementar('contatos_invalidos');
                    $resumo->resultado('impedido', 'contato_invalido');

                    continue;
                } finally {
                    $item->encerrar();
                }
            }

            if (Cache::add(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, now('America/Sao_Paulo')->addDay()->startOfDay())) {
                Mail::raw(
                    'Não há certificados pendentes para envio no momento.',
                    function ($message) {
                        $message->to('sistema@redemetrologica.com.br')->subject('Notificação: Sem certificados pendentes');
                    }
                );
            }

            if ($contatosInvalidos === 0) {
                $resumo->resultado('ignorado', 'sem_pendencias');
            }

            $this->info('Nenhum inscrito apto para envio. Consultas suspensas até a próxima meia-noite.');
        });
    }
}
