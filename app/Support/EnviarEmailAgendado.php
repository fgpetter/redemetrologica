<?php

namespace App\Support;

use App\Queue\ScheduledEmailGate;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\SentMessage;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\RoundRobinTransport;

class EnviarEmailAgendado
{
    public function execute(Mailable $email, string|array $destinatario, string|array|null $cc = null): void
    {
        $mailer = Mail::mailer();
        $transport = $mailer->getSymfonyTransport();

        if ($transport instanceof LogTransport || $transport instanceof ArrayTransport
            || $transport instanceof NullTransport || $transport instanceof RoundRobinTransport) {
            throw new RuntimeException('O envio agendado exige um transporte que confirme o aceite do provedor.');
        }

        $envio = $mailer->to($destinatario);

        if ($cc !== null) {
            $envio->cc($cc);
        }

        if (! ($envio->sendNow($email) instanceof SentMessage)) {
            throw new RuntimeException('O transporte não confirmou o aceite do e-mail agendado.');
        }

        app(ScheduledEmailGate::class)->aceitar();
    }
}
