<?php

namespace App\Mail;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InformacoesTreinamentoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(public array $dados) {}

    public static function paraInscrito(AgendaCursos $agenda, CursoInscrito $inscrito): self
    {
        $agenda->loadMissing(['curso', 'materiaisTreinamento']);

        return new self([
            'destinatario' => trim((string) $inscrito->email),
            'nome' => $inscrito->nome,
            'treinamento' => $agenda->curso->descricao,
            'data_curso' => $agenda->data_inicio?->toDateString(),
            'horario' => $agenda->horario,
            'link' => $agenda->link_aula,
            'materiais' => $agenda->materiaisTreinamento->map(fn ($material) => [
                'descricao' => $material->descricao ?: basename($material->arquivo),
                'url' => Storage::disk('public')->url($material->arquivo),
            ])->all(),
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Material e informações de acesso - '.$this->dados['treinamento'], replyTo: [new Address('eventos@redemetrologica.com.br')]);
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
