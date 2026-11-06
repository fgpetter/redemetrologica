<?php

namespace App\Actions\Avaliacoes;

use App\Mail\PesquisaSatisfacaoMail;
use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EnviarPesquisaSatisfacaoAction
{
    /**
     * Cria a pesquisa de satisfação (idempotente) e enfileira o e-mail.
     *
     * @param  AgendaAvaliacao  $avaliacao  Agenda a pesquisar
     * @return bool false quando a pesquisa foi criada mas o laboratório não tem e-mail
     */
    public function execute(AgendaAvaliacao $avaliacao): bool
    {
        $criada = DB::transaction(function () use ($avaliacao): bool {
            AgendaAvaliacao::query()->lockForUpdate()->findOrFail($avaliacao->getKey());

            if ($avaliacao->pesquisa()->exists()) {
                return false;
            }

            $avaliacao->pesquisa()->create([]);
            $avaliacao->update(['pesq_satisfacao' => now()->toDateString()]);

            return true;
        });

        if (! $criada) {
            return true;
        }

        $avaliacao->loadMissing('laboratorio');
        $email = trim((string) $avaliacao->laboratorio?->email);

        if ($email === '') {
            return false;
        }

        Mail::to($email)->queue((new PesquisaSatisfacaoMail($avaliacao))->afterCommit());

        return true;
    }
}
