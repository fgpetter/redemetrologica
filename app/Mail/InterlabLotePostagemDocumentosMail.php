<?php

namespace App\Mail;

use App\Models\InterlabLotePostagem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InterlabLotePostagemDocumentosMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public InterlabLotePostagem $lote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('interlab@redemetrologica.com.br', 'Interlaboratoriais Rede Metrológica RS')],
            subject: 'Documentos do lote de postagem — '.$this->lote->nome,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.interlab-lote-postagem-documentos',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $this->lote->loadMissing('etiquetas');

        $anexos = [];

        foreach ($this->lote->etiquetas as $indice => $etiqueta) {
            if (blank($etiqueta->etiqueta_path) || ! Storage::disk('local')->exists($etiqueta->etiqueta_path)) {
                continue;
            }

            $anexos[] = Attachment::fromStorageDisk('local', $etiqueta->etiqueta_path)
                ->as('etiqueta-'.($indice + 1).'.pdf')
                ->withMime('application/pdf');
        }

        if (filled($this->lote->dace_pdf_path)
            && Storage::disk('local')->exists($this->lote->dace_pdf_path)) {
            $anexos[] = Attachment::fromStorageDisk('local', $this->lote->dace_pdf_path)
                ->as('dace.pdf')
                ->withMime('application/pdf');
        } elseif (filled($this->lote->declaracao_conteudo_pdf_path)
            && Storage::disk('local')->exists($this->lote->declaracao_conteudo_pdf_path)) {
            $anexos[] = Attachment::fromStorageDisk('local', $this->lote->declaracao_conteudo_pdf_path)
                ->as('declaracao-conteudo.pdf')
                ->withMime('application/pdf');
        }

        return $anexos;
    }
}
