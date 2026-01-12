<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
class CertificadosDeletedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

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