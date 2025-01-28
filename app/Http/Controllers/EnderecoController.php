<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


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
    $validator = Validator::make($request->all(), [
        'pessoa_id' => ['required', 'integer'],
        'info' => ['nullable', 'string'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'complemento' => ['nullable', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
        'end_padrao' => ['nullable', 'integer']
      ],[
        'info.string' => 'Formato de texto inválido',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
        'end_padrao.integer' => 'Dado inválido'
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

      return back()
      ->withErrors($validator, 'principal')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    $prepared_data = $validator->validate();

    $endereco = Endereco::create([
      'pessoa_id' => $prepared_data['pessoa_id'],
      'info' => $prepared_data['info'],
      'cep' => $prepared_data['cep'],
      'endereco' => $prepared_data['endereco'],
      'complemento' => $prepared_data['complemento'],
      'cidade' => $prepared_data['cidade'],
      'uf' => $prepared_data['uf']
    ]);

    if (!$endereco) {
      return redirect()->back()->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    if (isset($request->end_padrao)) {
      $endereco->pessoa->update(['end_padrao' => $endereco->id]);
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
    $validator = Validator::make($request->all(), [
        'pessoa_id' => ['required', 'integer'],
        'info' => ['nullable', 'string'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'complemento' => ['nullable', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
        'end_padrao' => ['nullable', 'integer']
      ],[
        'info.string' => 'Formato de texto inválido',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
        'end_padrao.integer' => 'Dado inválido'
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

      return back()
      ->withErrors($validator, 'principal')
      ->withInput()
      ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    $prepared_data = $validator->validate();
    $endereco->update([
      'pessoa_id' => $prepared_data['pessoa_id'],
      'info' => $prepared_data['info'],
      'cep' => $prepared_data['cep'],
      'endereco' => $prepared_data['endereco'],
      'complemento' => $prepared_data['complemento'],
      'cidade' => $prepared_data['cidade'],
      'uf' => $prepared_data['uf']
    ]);

    if (isset($request->end_padrao)) {
      $endereco->pessoa->update(['end_padrao' => $endereco->id]);
    } elseif ($endereco->pessoa->end_padrao == $endereco->id) {
      $endereco->pessoa->update(['end_padrao' => null]);
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
    $endereco->pessoa->update(['end_padrao' => null]);
    $endereco->delete();

    return redirect()->back()->with('warning', 'Endereco removido');
  }
}
