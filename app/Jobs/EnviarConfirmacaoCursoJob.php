<?php

namespace App\Jobs;

use App\Mail\ConfirmacaoCursoMail;
use App\Models\CursoInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarConfirmacaoCursoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $inscritoId,
        public string|array $email,
        public string $curso,
        public string $dataInicio,
    ) {}

    public function handle(): void
    {
        $resumo = new ResumoExecucao('curso.confirmacao', ['inscrito_id' => $this->inscritoId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->resultado('ignorado', 'ja_confirmado_ou_ausente');
        $resumo->executar(function ($resumo): void {
            DB::transaction(function () use ($resumo) {
                $inscrito = $resumo->etapa('carregar_registros', fn () => CursoInscrito::query()->lockForUpdate()->find($this->inscritoId));

                if ($resumo->etapa('validar_elegibilidade', fn () => ! $inscrito || $inscrito->confirmacao_enviada_em !== null)) {
                    $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                    return;
                }

                $resumo->etapa('enviar', fn () => Mail::to($this->email)->sendNow(new ConfirmacaoCursoMail($this->curso, $this->dataInicio)));
                $resumo->etapa('confirmar_estado', fn () => $inscrito->update(['confirmacao_enviada_em' => now()]));
                $resumo->contexto(['inscrito_uid' => $inscrito->uid]);
                $resumo->resultado('aceito_transporte');
            });
        }, job: true);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Falha ao enviar confirmação de treinamento.', [
            'inscrito_id' => $this->inscritoId,
            'evento' => 'curso.confirmacao_falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }
}
