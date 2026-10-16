<?php

namespace App\Actions\Suporte;

use App\Models\SuporteDemanda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrdenarDemandasSuporteAction
{
    /**
     * @param  array{busca?: string, status?: string}  $filtros
     */
    public function execute(User $user, string $uid, string $direcao, array $filtros): void
    {
        SuporteDemanda::autorizarAcesso($user);
        Validator::make(['direcao' => $direcao], ['direcao' => [Rule::in(['subir', 'descer'])]])->validate();
        DB::transaction(function () use ($uid, $direcao, $filtros): void {
            SuporteDemanda::bloquearFila();
            $demanda = SuporteDemanda::comFiltros($filtros)->where('uid', $uid)->lockForUpdate()->firstOrFail();
            $subir = $direcao === 'subir';
            $vizinha = SuporteDemanda::comFiltros($filtros)
                ->where('prioridade', $subir ? '<' : '>', $demanda->prioridade)
                ->orderBy('prioridade', $subir ? 'desc' : 'asc')->lockForUpdate()->first();
            if (! $vizinha) {
                return;
            }
            $prioridade = $demanda->prioridade;
            $demanda->update(['prioridade' => $vizinha->prioridade]);
            $vizinha->update(['prioridade' => $prioridade]);
        });
    }
}
