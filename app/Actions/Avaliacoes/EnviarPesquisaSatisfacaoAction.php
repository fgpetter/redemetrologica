<?php

namespace App\Actions\Avaliacoes;

use App\Mail\PesquisaSatisfacaoMail;
use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\Mail;

class EnviarPesquisaSatisfacaoAction
{
    /**
     * Cria a pesquisa de satisfação (idempotente) e enfileira o e-mail.
     *
     * @return bool false quando a pesquisa foi criada mas o laboratório não tem e-mail
     */
    public function execute(AgendaAvaliacao $avaliacao): bool
    {
        if ($avaliacao->pesquisa()->exists()) {
            return true;
        }

        $avaliacao->pesquisa()->create([]);
        $avaliacao->update(['pesq_satisfacao' => now()->toDateString()]);

        $avaliacao->loadMissing('laboratorio');
        $email = trim((string) $avaliacao->laboratorio?->email);

        if ($email === '') {
            return false;
        }

        Mail::to($email)->queue(new PesquisaSatisfacaoMail($avaliacao));

        return true;
    }
}
