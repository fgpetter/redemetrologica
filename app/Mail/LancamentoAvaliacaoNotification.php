<?php

namespace App\Mail;

use App\Models\AgendaAvaliacao;
use App\Models\LancamentoFinanceiro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class LancamentoAvaliacaoNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    public array $dados_email = [];

    /**
     * Create a new message instance.
     */
    public function __construct(AgendaAvaliacao $avaliacao, LancamentoFinanceiro $lancamento)
    {
        $avaliacao->loadMissing('laboratorio');

        $this->dados_email = [
            'laboratorio_nome' => $avaliacao->laboratorio->nome_laboratorio,
            'data_inicio' => Carbon::parse($avaliacao->data_inicio)->format('d/m/Y'),
            'valor' => formataValorBr($lancamento->valor),
            'historico' => $lancamento->historico,
            'link_lancamento' => route('lancamento-financeiro-insert', $lancamento->uid),
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo lançamento financeiro - '.$this->dados_email['laboratorio_nome'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lancamento-avaliacao',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
