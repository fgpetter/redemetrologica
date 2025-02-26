<?php

namespace App\Http\Controllers;

use App\Models\DadoBancario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class DadoBancarioController extends Controller
{

  /**
   * Salva dados bancários
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function save(Request $request): RedirectResponse
  {
    $validator = Validator::make(
      $request->all(),
      [
        'conta_id' => ['nullable', 'exists:dados_bancarios,uid'],
        'pessoa_id' => ['required', 'exists:pessoas,id'],
        'nome_banco' => ['nullable', 'string'],
        'cod_banco' => ['nullable', 'string'],
        'agencia' => ['nullable', 'string'],
        'conta' => ['nullable', 'string'],
      ],[
        'nome_banco.string' => 'Dado inválido',
        'cod_banco.string' => 'Dado inválido',
        'agencia.string' => 'Dado inválido',
        'conta.string' => 'Dado inválido',
      ]
    );

    if ($validator->fails()) {
      
      Log::channel('validation')->info("Erro de validação", 
      [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) .'::'. __FUNCTION__ ,
          'errors' => $validator->errors() ?? null,
      ]);

      return redirect()->back()
        ->with('error', 'Dados informados não são válidos')
        ->withErrors($validator);
    }

    $conta = DadoBancario::updateOrCreate(
      [
        'uid' => $request->get('conta_id') ?? uniqid(),
      ],[
        'pessoa_id' => $request->get('pessoa_id'),
        'nome_conta' => $request->get('nome_conta'),
        'nome_banco' => $request->get('nome_banco'),
        'cod_banco' => $request->get('cod_banco'),
        'agencia' => $request->get('agencia'),
        'conta' => $request->get('conta'),
      ]
    );

    if (!$conta) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    return redirect()->back()->with('success', 'Conta cadastrada com sucesso');
  }

  /**
   * Remove unidade
   *
   * @param DadoBancario $conta
   * @return RedirectResponse
   **/
  public function delete(DadoBancario $conta): RedirectResponse
  {
    $conta->delete();

    return redirect()->back()->with('warning', 'Conta removida');
  }
}
