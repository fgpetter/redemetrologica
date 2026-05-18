<?php

namespace App\Jobs;

use App\Exceptions\InvalidEmailException;
use App\Mail\ConfirmacaoInterlabMail;
use App\Models\InterlabInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarConfirmacaoInterlabJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $participante;

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
    public function __construct(InterlabInscrito $participante)
    {
        $this->participante = $participante;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->participante->pessoa || ! $this->participante->pessoa->email) {
            Log::warning('Tentativa de envio de confirmação para participante sem email de responsável cadastrado.', [
                'participante_id' => $this->participante->id,
            ]);

            return;
        }

        if (empty($this->participante->pessoa->email)) {
            $content = [
                'class' => self::class,
                'participante_id' => $this->participante->id,
            ];
            new InvalidEmailException($content);
        } else {
            Mail::to($this->participante->pessoa->email)
                ->send(new ConfirmacaoInterlabMail($this->participante));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar email de confirmação para participante ID: '.$this->participante->id, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
