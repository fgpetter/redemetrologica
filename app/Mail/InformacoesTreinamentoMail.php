<?php

namespace App\Mail;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
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

class InformacoesTreinamentoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public string $data;

    public function __construct(public AgendaCursos $agenda, public CursoInscrito $inscrito, ?string $data = null)
    {
        $this->data = $data ?? $agenda->data_inicio->toDateString();
        $inicio = CarbonImmutable::parse($this->data, 'America/Sao_Paulo')->startOfDay()->subDay()->setTime(6, 0);
        $this->delay($inicio);
    }

    public function send($mailer)
    {
        return DB::transaction(function () use ($mailer) {
            $inscrito = CursoInscrito::query()->whereKey($this->inscrito->id)->lockForUpdate()->first();
            if (! $inscrito || $inscrito->informacoes_enviadas_em) {
                return null;
            }

            $inicio = CarbonImmutable::parse($this->data, 'America/Sao_Paulo')->startOfDay()->subDay()->setTime(6, 0);
            $agora = now('America/Sao_Paulo');
            if ($agora->lt($inicio) || $agora->gte($inicio->addDay())) {
                Log::warning('E-mail automático de curso fora da janela prevista; envio ignorado.', [
                    'agenda_curso_id' => $this->agenda->id,
                    'inscrito_id' => $inscrito->id,
                    'tipo' => 'participante',
                    'atual' => $agora->toDateTimeString(),
                ]);

                return null;
            }

            $enviado = parent::send($mailer);
            if ($enviado !== null) {
                $inscrito->update(['informacoes_enviadas_em' => now()]);
            }

            return $enviado;
        });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Falha definitiva no envio de e-mail automático de curso.', [
            'agenda_curso_id' => $this->agenda->id,
            'inscrito_id' => $this->inscrito->id,
            'data_curso' => $this->data,
            'tipo' => 'participante',
            'exception' => $exception,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Material e informações de acesso - '.$this->agenda->curso->descricao, replyTo: [new Address('eventos@redemetrologica.com.br')]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.informacoes-treinamento');
    }

    public function attachments(): array
    {
        return [];
    }
}
