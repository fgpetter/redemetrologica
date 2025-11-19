<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use App\Models\InterlabInscrito;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\SenhaInterlabNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EnviarSenhaInterlabJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $participanteId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

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
        try {
            $participante = InterlabInscrito::with(['laboratorio', 'agendaInterlab.interlab'])
                ->findOrFail($this->participanteId);

            $labNameSlug = Str::slug($participante->laboratorio->nome);
            $fileName = 'tag_senha_' . $labNameSlug . '_' . $participante->id . '.pdf';
            $path = 'public/docs/senhas/' . $fileName;


            if (!Storage::exists('public/docs/senhas')) {
                Storage::makeDirectory('public/docs/senhas');
            }

            if (Storage::exists($path)) {
                Storage::delete($path);
            }

            Pdf::view('certificados.tag-senha', [
                'participante' => $participante,
            ])->save(Storage::path($path));

            Mail::mailer('interlaboratorial')
                ->to($participante->laboratorio->email)
                ->cc('sistema@redemetrologica.com.br')
                ->queue(new SenhaInterlabNotification($participante, Storage::path($path)));

        } catch (\Exception $e) {
            Log::error('Falha ao enviar senha interlab para participante ID: ' . $this->participanteId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
