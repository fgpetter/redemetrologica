<?php

namespace App\Jobs;

use App\Mail\DocumentoRodadaMail;
use App\Models\InterlabInscrito;
use App\Models\InterlabRodada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarDocumentoRodadaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public InterlabInscrito $inscrito;

    public InterlabRodada $rodada;

    public string $tipoDocumento;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(InterlabInscrito $inscrito, InterlabRodada $rodada, string $tipoDocumento)
    {
        $this->inscrito = $inscrito;
        $this->rodada = $rodada;
        $this->tipoDocumento = $tipoDocumento;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->inscrito->pessoa || ! $this->inscrito->pessoa->email) {
            Log::warning('Tentativa de envio de documento de rodada para inscrito sem email de responsável cadastrado.', [
                'inscrito_id' => $this->inscrito->id,
                'rodada_id' => $this->rodada->id,
                'tipo_documento' => $this->tipoDocumento,
            ]);

            return;
        }

        $mail = Mail::to($this->inscrito->pessoa->email);

        if (! empty($this->inscrito->email)) {
            $mail->cc($this->inscrito->email);
        }

        $mail->send(new DocumentoRodadaMail($this->inscrito, $this->rodada, $this->tipoDocumento));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar email de documento de rodada para inscrito ID: '.$this->inscrito->id, [
            'rodada_id' => $this->rodada->id,
            'tipo_documento' => $this->tipoDocumento,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
