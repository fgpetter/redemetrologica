<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;


class EnderecoController extends Controller
{

    /**
   * Adiciona endereco
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate([
      'pessoa' => ['required', 'integer'],
      'cep' => ['required', 'string'],
      'endereco' => ['required', 'string'],
      'cidade' => ['required', 'string'],
      'estado' => ['required', 'string'],
      ],[
        'nome.required' => 'Preencha o campo nome ou razão social',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'estado.required' => 'Preencha o estado',
      ]
    );

    $endereco = Endereco::create([
      'uid' => substr(hrtime(true), -9, 9),
      'pessoa_id' => $request->get('pessoa'),
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf'),

    ]);

    if(!$endereco){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    return redirect()->back()->with('succes', 'Pessoa cadastrada com sucesso');
  }


  /**
   * Edita dados de endereco
   *
   * @param Request $request
   * @param Endereco $endereco
   * @return RedirectResponse
   **/
  public function update(Request $request, Endereco $endereco): RedirectResponse
  {

    $request->validate([
      'pessoa' => ['required', 'integer'],
      'cep' => ['required', 'string'],
      'endereco' => ['required', 'string'],
      'cidade' => ['required', 'string'],
      'estado' => ['required', 'string'],
      ],[
        'nome.required' => 'Preencha o campo nome ou razão social',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'estado.required' => 'Preencha o estado',
      ]
    );


    $endereco->update([
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    return redirect()->back()->with('endereco-success', 'Usuário atualizado');
  }


  /**
   * Remove endereço
   *
   * @param Endereco $user
   * @return RedirectResponse
   **/
  public function delete(Endereco $endereco): RedirectResponse
  {
    if($endereco->unidade_id){
      return redirect()->back()->with('endereco-error', 'Endereço atrelado a unidade não pode ser removido');
    }

    $endereco->delete();

    return redirect()->back()->with('endereco-success', 'Endereco removido');
  }

}