<?php

namespace App\Jobs;

use App\Mail\ConfirmacaoCursoMail;
use App\Models\CursoInscrito;
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
        public string $email,
        public string $curso,
        public string $dataInicio,
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $inscrito = CursoInscrito::query()->lockForUpdate()->find($this->inscritoId);

            if (! $inscrito || $inscrito->confirmacao_enviada_em !== null) {
                return;
            }

            Mail::to($this->email)->sendNow(new ConfirmacaoCursoMail($this->curso, $this->dataInicio));
            $inscrito->update(['confirmacao_enviada_em' => now()]);
        });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Falha ao enviar confirmação de treinamento.', [
            'inscrito_id' => $this->inscritoId,
            'exception' => $exception,
        ]);
    }
}
