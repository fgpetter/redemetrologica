<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificadosDeletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $deletedFiles;

    public function __construct(array $deletedFiles)
    {
        $this->deletedFiles = $deletedFiles;
    }

    public function build()
    {
        return $this->subject('Relatório de Certificados Removidos')
                    ->view('emails.certificados-deleted');
    }
}