<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\InterlabInscrito;
use Illuminate\Queue\SerializesModels;

class SenhaInterlabNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $participante;
    public $pdfPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(InterlabInscrito $participante, $pdfPath)
    {
        $this->participante = $participante;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Código de Identificação - ' . $this->participante->agendaInterlab->interlab->nome)
            ->view('emails.senha-interlab')
            ->attach($this->pdfPath, [
                'as' => 'carta-senha.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
