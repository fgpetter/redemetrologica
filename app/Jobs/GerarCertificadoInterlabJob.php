<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Models\InterlabInscrito;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificadoInterlabMail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GerarCertificadoInterlabJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $participanteId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 300; // 5 minutos para geração de PDF

    /**
     * Create a new job instance.
     */
    public function __construct($participanteId)
    {
        $this->participanteId = $participanteId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $participante = InterlabInscrito::with(['laboratorio', 'agendaInterlab.interlab'])
            ->findOrFail($this->participanteId);

        $labNameSlug = Str::slug($participante->laboratorio->nome);
        $fileName = 'certificado_interlab_' . $labNameSlug . '_' . $participante->agendaInterlab->interlab->id . '.pdf';
        $Path = 'public/docs/certificados/' . $fileName;

        // Atualizar dados do participante
        $participante->certificado_emitido = now();
        $participante->certificado_path = $fileName;
        $participante->save();

        // Remover arquivo existente, se houver
        if (Storage::exists($Path)) {
            Storage::delete($Path);
        }

        // Gerar PDF do certificado
        Pdf::view('certificados.certificado-interlab', [
            'participante' => $participante,
        ])->save(Storage::path($Path));

        // Enviar email com certificado
        Mail::to($participante->laboratorio->email)
            ->queue(new CertificadoInterlabMail($participante, Storage::path($Path)));
    }

    public function failed(\Throwable $exception): void
    {
        // Log do erro ou notificação para administradores
        Log::error('Falha ao gerar certificado para participante ID: ' . $this->participanteId, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
