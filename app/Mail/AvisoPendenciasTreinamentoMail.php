<?php

namespace App\Mail;

use App\Models\AgendaCursos;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AvisoPendenciasTreinamentoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public string $data;

    public function __construct(public AgendaCursos $agenda, ?string $data = null)
    {
        $this->data = $data ?? $agenda->data_inicio->toDateString();
        $this->delay($this->inicioJanela());
    }

    private function inicioJanela(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->data, 'America/Sao_Paulo')
            ->startOfDay()->subDays(1)->setTime(6, 0);
    }

    public function send($mailer)
    {
        return DB::transaction(function () use ($mailer) {
            $agenda = AgendaCursos::query()->whereKey($this->agenda->id)->lockForUpdate()->first();
            if (! $agenda || $agenda->pendencias_enviadas_em) {
                return null;
            }

            $agora = now('America/Sao_Paulo');
            $inicio = $this->inicioJanela();
            if ($agora->lt($inicio) || $agora->gte($inicio->addDay())) {
                Log::warning('E-mail automático de curso fora da janela prevista; envio ignorado.', [
                    'agenda_curso_id' => $agenda->id,
                    'tipo' => 'pendencias',
                    'atual' => $agora->toDateTimeString(),
                ]);

                return null;
            }

            $enviadoAgora = parent::send($mailer);
            if ($enviadoAgora !== null) {
                $agenda->update(['pendencias_enviadas_em' => now()]);
            }

            return $enviadoAgora;
        });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Falha definitiva no envio de e-mail automático de curso.', [
            'agenda_curso_id' => $this->agenda->id,
            'inscrito_id' => null,
            'data_curso' => $this->data,
            'tipo' => 'pendencias',
            'exception' => $exception,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pendências de link e materiais', replyTo: [new Address('eventos@redemetrologica.com.br')]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.aviso-pendencias-treinamento');
    }

    public function attachments(): array
    {
        return [];
    }
}
