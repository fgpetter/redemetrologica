<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class UnidadeController extends Controller
{

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $validator = Validator::make(
      $request->all(),
      [
        'nome' => ['required', 'string', 'max:191'],
        'pessoa' => ['required', 'integer'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
      ],
      [
        'nome.required' => 'Preencha o campo nome ou razão social',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o estado',
      ]
    );

    if ($validator->fails()) {
      return redirect()->back()
        ->withInput()
        ->with('error', 'Dados informados não são válidos')
        ->withErrors($validator);
    }


    $endereco = Endereco::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $request->get('pessoa'),
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    $unidade = Unidade::create([
      'uid' => config('hashing.uid'),
      'pessoa_id' => $request->get('pessoa'),
      'endereco_id' => $endereco->id,
      'nome' => strtoupper($request->get('nome')),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
      'cod_laboratorio' => $request->get('cod_laboratorio'),
      'nome_responsavel' => $request->get('nome_responsavel'),
      'responsavel_tecnico' => $request->get('responsavel_tecnico'),
    ]);

    if (!$unidade) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    $endereco->update(['unidade_id' => $unidade->id]);

    return redirect()->back()->with('success', 'Unidade cadastrada com sucesso');
  }

  /**
   * Edita dados de unidade
   *
   * @param Request $request
   * @param Unidade $unidade
   * @return RedirectResponse
   **/
  public function update(Request $request, Unidade $unidade): RedirectResponse
  {

    $request->validate(
      [
        'nome' => ['required', 'string', 'max:191'],
        'pessoa' => ['required', 'integer'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'cidade' => ['required', 'string'],
        'estado' => ['required', 'string'],
      ],
      [
        'nome.required' => 'Preencha o campo nome ou razão social',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'estado.required' => 'Preencha o estado',
      ]
    );

    $unidade->update([
      'nome' => strtoupper($request->get('nome')),
      'telefone' => $request->get('telefone'),
      'email' => $request->get('email'),
      'cod_laboratorio' => $request->get('cod_laboratorio'),
      'nome_responsavel' => $request->get('nome_responsavel'),
      'responsavel_tecnico' => $request->get('responsavel_tecnico'),
    ]);

    $endereco = Endereco::find($unidade->endereco_id);
    $endereco->update([
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);


    return redirect()->route('user-index')->with('success', 'Unidade atualizada');
  }

  /**
   * Remove unidade
   *
   * @param Unidade $user
   * @return RedirectResponse
   **/
  public function delete(Unidade $unidade): RedirectResponse
  {
    $unidade->delete();

    return redirect()->back()->with('warning', 'Unidade removida');
  }
}
