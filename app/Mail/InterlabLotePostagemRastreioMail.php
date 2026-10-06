<?php

namespace App\Mail;

use App\Models\InterlabLotePostagemItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterlabLotePostagemRastreioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public InterlabLotePostagemItem $item) {}

    public function envelope(): Envelope
    {
        $codigo = $this->item->codigo_objeto ?? $this->item->uid;

        return new Envelope(
            replyTo: [new Address('interlab@redemetrologica.com.br', 'Interlaboratoriais Rede Metrológica RS')],
            subject: 'Atualização de rastreio — '.$codigo,
        );
    }

    public function content(): Content
    {
        $this->item->loadMissing('inscrito');

        $codigoObjeto = (string) ($this->item->codigo_objeto ?? '');

        return new Content(
            view: 'emails.interlab-lote-postagem-rastreio',
            with: [
                'responsavelTecnico' => $this->item->inscrito?->responsavel_tecnico ?: 'responsável técnico',
                'nomeLaboratorio' => (string) ($this->item->destinatario['nome'] ?? 'laboratório'),
                'enderecoCompleto' => $this->enderecoCompleto(),
                'codigoObjeto' => $codigoObjeto,
                'statusAtual' => $this->item->ultimo_evento ?: $this->item->status_correios,
                'urlRastreamento' => 'https://rastreamento.correios.com.br/app/index.php',
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function enderecoCompleto(): string
    {
        $endereco = $this->item->destinatario['endereco'] ?? [];

        if (! is_array($endereco)) {
            return '';
        }

        $partes = array_filter([
            trim(implode(', ', array_filter([
                $endereco['logradouro'] ?? null,
                $endereco['numero'] ?? null,
            ]))),
            $endereco['complemento'] ?? null,
            $endereco['bairro'] ?? null,
            trim(implode(' — ', array_filter([
                $endereco['cidade'] ?? null,
                $endereco['uf'] ?? null,
            ]))),
            filled($endereco['cep'] ?? null) ? 'CEP '.$endereco['cep'] : null,
        ]);

        return implode(', ', $partes);
    }
}
