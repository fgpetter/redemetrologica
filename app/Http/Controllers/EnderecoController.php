<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Http\Request;
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
    $validated = $request->validate(
      [
        'info' => ['nullable', 'string'],
        'pessoa' => ['required', 'integer'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
        'end_padrao' => ['nullable', 'integer']
      ],
      [
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
        'end_padrao.integer' => 'Dado inválido'
      ]);

    $endereco = Endereco::create($validated);

    if (!$endereco) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    if (isset($request->end_padrao)) {
      Pessoa::find($request->pessoa)->update(['end_padrao' => $endereco->id]);
    }

    return redirect()->back()->with('success', 'Endereço cadastrado com sucesso');
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
    $validated = $request->validate(
      [
        'info' => ['nullable', 'string'],
        'pessoa' => ['required', 'integer'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
        'end_padrao' => ['nullable', 'integer']
      ],
      [
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
        'end_padrao.integer' => 'Dado inválido'
      ]);

    $endereco->update($validated);

    $pessoa = Pessoa::find($request->pessoa);

    if (isset($request->end_padrao)) {
      $pessoa->update(['end_padrao' => $endereco->id]);
    } elseif ($pessoa->end_padrao == $endereco->id) {
      $pessoa->update(['end_padrao' => null]);
    }

    return redirect()->back()->with('success', 'Endereço atualizado');
  }

  /**
   * Remove endereço
   *
   * @param Endereco $user
   * @return RedirectResponse
   **/
  public function delete(Endereco $endereco): RedirectResponse
  {
    $endereco->delete();

    return redirect()->back()->with('warning', 'Endereco removido');
  }
}
