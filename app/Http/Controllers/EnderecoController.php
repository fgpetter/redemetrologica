<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

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
    $validator = Validator::make($request->all(),[
      'pessoa' => ['required', 'integer'],
      'cep' => ['required', 'string'],
      'endereco' => ['required', 'string'],
      'cidade' => ['required', 'string'],
      'uf' => ['required', 'string'],
      ],[
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
      ]
    );

    if ($validator->fails()) {
      return redirect()->back()
        ->with('endereco-error', 'Dados de endereço não são válidos')
        ->withErrors($validator);
    }

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
      return redirect()->back()->withInput($request->input())->with('endereco-error', 'Ocorreu um erro!');
    }

    return redirect()->back()->with('endereco-success', 'Endereço cadastrado com sucesso');
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
    $validator = Validator::make($request->all(),[
      'cep' => ['required', 'string'],
      'endereco' => ['required', 'string'],
      'cidade' => ['required', 'string'],
      'uf' => ['required', 'string'],
      ],[
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o estado',
      ]
    );

    if ($validator->fails()) {
      return redirect()->back()
        ->with('endereco-error', 'Dados de endereço não são válidos')
        ->withErrors($validator);
    } 

    $endereco->update([
      'endereco' => $request->get('endereco'),
      'complemento' => $request->get('complemento'),
      'bairro' => $request->get('bairro'),
      'cep' => $request->get('cep'),
      'cidade' => $request->get('cidade'),
      'uf' => $request->get('uf')
    ]);

    return redirect()->back()->with('endereco-success', 'Endereço atualizado');
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