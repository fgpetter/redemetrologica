<?php

namespace App\Http\Controllers;

use App\Http\Requests\DadoBancarioRequest;
use App\Models\DadoBancario;
use Illuminate\Http\RedirectResponse;

class DadoBancarioController extends Controller
{
    /**
     * Salva dados bancários
     *
     **/
    public function save(DadoBancarioRequest $request): RedirectResponse
    {

        $conta = DadoBancario::updateOrCreate(
            [
                'uid' => $request->get('conta_id') ?? uniqid(),
            ], [
                'pessoa_id' => $request->get('pessoa_id'),
                'nome_conta' => $request->get('nome_conta'),
                'nome_banco' => $request->get('nome_banco'),
                'cod_banco' => $request->get('cod_banco'),
                'agencia' => $request->get('agencia'),
                'conta' => $request->get('conta'),
            ]
        );

        if (! $conta) {
            return redirect()->back()->with('error', 'Ocorreu um erro!');
        }

        return redirect()->back()->with('success', 'Conta cadastrada com sucesso');
    }

    /**
     * Remove unidade
     *
     **/
    public function delete(DadoBancario $conta): RedirectResponse
    {
        $conta->delete();

        return redirect()->back()->with('warning', 'Conta removida');
    }
}
