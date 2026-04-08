<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Endereco;
use App\Models\InterlabLaboratorio;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        'pessoa_id' => ['required', 'integer', 'exists:pessoas,id'],
        'info' => ['nullable', 'string'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'complemento' => ['nullable', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
        'tipo_endereco' => ['required', 'in:principal,cobranca'],
      ],[
        'info.string' => 'Formato de texto inválido',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
        'tipo_endereco.required' => 'Selecione o tipo de endereço',
        'tipo_endereco.in' => 'Tipo de endereço inválido',
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
    $pessoa = Pessoa::findOrFail($prepared_data['pessoa_id']);

    DB::transaction(function () use ($prepared_data, $pessoa) {
      $endereco = Endereco::create([
        'info' => $prepared_data['info'],
        'cep' => $prepared_data['cep'],
        'endereco' => $prepared_data['endereco'],
        'complemento' => $prepared_data['complemento'],
        'cidade' => $prepared_data['cidade'],
        'uf' => $prepared_data['uf']
      ]);

      $fkColumn = $prepared_data['tipo_endereco'] === 'cobranca'
        ? 'endereco_cobranca_id'
        : 'endereco_id';

      $pessoa->update([$fkColumn => $endereco->id]);
    });

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
        'info' => ['nullable', 'string'],
        'cep' => ['required', 'string'],
        'endereco' => ['required', 'string'],
        'complemento' => ['nullable', 'string'],
        'cidade' => ['required', 'string'],
        'uf' => ['required', 'string'],
      ],[
        'info.string' => 'Formato de texto inválido',
        'cep.required' => 'Preencha o campo CEP',
        'endereco.required' => 'Preencha o campo endereço',
        'cidade.required' => 'Preencha o campo cidade',
        'uf.required' => 'Preencha o uf',
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
      'info' => $prepared_data['info'],
      'cep' => $prepared_data['cep'],
      'endereco' => $prepared_data['endereco'],
      'complemento' => $prepared_data['complemento'],
      'cidade' => $prepared_data['cidade'],
      'uf' => $prepared_data['uf']
    ]);

    return redirect()->back()->with('success', 'Endereço atualizado');
  }

  /**
   * Remove endereço
   *
   * @param Endereco $user
   * @return RedirectResponse
   **/
  public function delete(Endereco $endereco, InterlabLaboratorio $laboratorio): RedirectResponse
  {

    // Verifica se o endereço está vinculado a algum PEP e impede a remoção
    if ($laboratorio->where('endereco_id', $endereco->id)->exists()) {
      return redirect()->back()->with('error', 'Não é possível remover este endereço, pois o mesmo está vinculado a um PEP.');
    }

    DB::transaction(function () use ($endereco) {
      Pessoa::where('endereco_id', $endereco->id)->update(['endereco_id' => null]);
      Pessoa::where('endereco_cobranca_id', $endereco->id)->update(['endereco_cobranca_id' => null]);
      $endereco->delete();
    });

    return redirect()->back()->with('warning', 'Endereco removido');
  }

  public function check(Request $request)
  {
    $endereco = Endereco::select(['endereco','bairro','cidade','uf'])
      ->where( ['cep' => $request->cep ] )
      ->firstOr( fn() => ['error' => 'No results'] );
    return response()->json( $endereco );
  }

}
