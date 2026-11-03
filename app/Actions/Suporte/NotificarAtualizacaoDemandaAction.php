<?php

namespace App\Actions\Suporte;

use App\Mail\SuporteDemandaAtualizadaMail;
use App\Models\SuporteDemanda;
use Illuminate\Support\Facades\Mail;

class NotificarAtualizacaoDemandaAction
{
    public function execute(SuporteDemanda $demanda): void
    {
        $url = route('suporte-insert', ['demanda' => $demanda->uid]);

        foreach ($demanda->participantes()->whereNotNull('email')->get() as $participante) {
            if (blank($participante->email)) {
                continue;
            }

            Mail::to($participante->email)->queue(
                (new SuporteDemandaAtualizadaMail($demanda->titulo, $url))->afterCommit()
            );
        }
    }
}
