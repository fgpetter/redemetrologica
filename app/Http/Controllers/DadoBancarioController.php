<?php

namespace App\Http\Controllers;

use App\Models\Avaliador;
use App\Models\DadoBancario;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class DadoBancarioController extends Controller
{

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $validator = Validator::make($request->all(),[
      'nome_banco' => ['required', 'string'],
      'cod_banco' => ['required', 'string'],
      'agencia' => ['required', 'string'],
      'conta' => ['required', 'string'],
      ],[
        'nome_banco.required' => 'Preencha o nome do banco',
        'cod_banco.required' => 'Preencha o código do banco',
        'agencia.required' => 'Preencha a agência',
        'conta' => 'Preencha a conta',
      ]
    );

    if ($validator->fails()) {
      return redirect()->back()
        ->withInput($request->input())
        ->with('unidade-error', 'Dados informados não são válidos')
        ->withErrors($validator);
    }

    $conta = DadoBancario::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $request->get('pessoa_id'),
      'nome_conta' => $request->get('nome_conta'),
      'nome_banco' => $request->get('nome_banco'),
      'cod_banco' => $request->get('cod_banco'),
      'agencia' => $request->get('agencia'),
      'conta' => $request->get('conta'),
    ]);

    if(!$conta){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    if($request->get('conta_padrao')) {
      Funcionario::where('pessoa_id', $request->get('pessoa_id'))->first()->update(['conta_padrao' => $conta->id]);
    }

    if($request->get('conta_padrao_avaliador')) {
      Avaliador::where('pessoa_id', $request->get('pessoa_id'))->first()->update(['conta_padrao' => $conta->id]);
    }


    return redirect()->back()->with('conta-success', 'Conta cadastrada com sucesso');
  }

  /**
   * Edita dados de unidade
   *
   * @param Request $request
   * @param DadoBancario $unidade
   * @return RedirectResponse
   **/
  public function update(Request $request, DadoBancario $conta): RedirectResponse
  {
    $validator = Validator::make($request->all(),[
      'nome_banco' => ['required', 'string'],
      'cod_banco' => ['required', 'string'],
      'agencia' => ['required', 'string'],
      'conta' => ['required', 'string'],
      ],[
        'nome_banco.required' => 'Preencha o nome do banco',
        'cod_banco.required' => 'Preencha o código do banco',
        'agencia.required' => 'Preencha a agência',
        'conta' => 'Preencha a conta',
      ]
    );

    if ($validator->fails()) {
      return redirect()->back()
        ->withInput($request->input())
        ->with('unidade-error', 'Dados informados não são válidos')
        ->withErrors($validator);
    }

    $conta->update([
      'nome_conta' => $request->get('nome_conta'),
      'nome_banco' => $request->get('nome_banco'),
      'cod_banco' => $request->get('cod_banco'),
      'agencia' => $request->get('agencia'),
      'conta' => $request->get('conta'),
    ]);

    if(!$conta){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    if($request->get('conta_padrao')) {
      Funcionario::where('pessoa_id', $request->get('pessoa_id'))->first()->update(['conta_padrao' => $conta->id]);
    }

    if($request->get('conta_padrao_avaliador')) {
      Avaliador::where('pessoa_id', $request->get('pessoa_id'))->first()->update(['conta_padrao' => $conta->id]);
    }

    return redirect()->back()->with('conta-success', 'Conta cadastrada com sucesso');

  }

  /**
   * Remove unidade
   *
   * @param DadoBancario $user
   * @return RedirectResponse
   **/
  public function delete(DadoBancario $conta): RedirectResponse
  {
    $conta->delete();

    return redirect()->back()->with('conta-success', 'Conta removida');
  }

}