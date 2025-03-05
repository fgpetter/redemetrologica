<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class UnidadeController extends Controller
{

  /**
   * Adiciona unidade a empresa
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
        'pessoa' => ['required', 'string', 'exists:pessoas,uid'],
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
      Log::channel('validation')->info(
        "Erro de validação",
        [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this) . '::' . __FUNCTION__,
          'errors' => $validator->errors() ?? null,
        ]
      );

      return redirect()->back()
        ->withInput()
        ->with('error', 'Dados informados não são válidos')
        ->withErrors($validator);
    }

    $prepared_data = $validator->validate();

    $query = DB::transaction(function () use ($prepared_data) {

      $endereco = Endereco::create([
        'pessoa_id' => $prepared_data['pessoa'],
        'endereco' => $prepared_data['endereco'],
        'complemento' => $prepared_data['complemento'] ?? null,
        'bairro' => $prepared_data['bairro'] ?? null,
        'cep' => $prepared_data['cep'],
        'cidade' => $prepared_data['cidade'],
        'uf' => $prepared_data['uf']
      ]);

      $unidade = Unidade::create([
        'pessoa_id' => $prepared_data['pessoa'],
        'endereco_id' => $endereco->id,
        'nome' => strtoupper($prepared_data['nome']),
        'telefone' => $prepared_data['telefone'] ?? null,
        'email' => $prepared_data['email'] ?? null,
        'cod_laboratorio' => $prepared_data['cod_laboratorio'] ?? null,
        'nome_responsavel' => $prepared_data['nome_responsavel'] ?? null,
        'responsavel_tecnico' => $prepared_data['responsavel_tecnico'] ?? null,
      ]);

      if (!$unidade) {
        return redirect()->back()->with('error', 'Ocorreu um erro!');
      }

      return compact($endereco, $unidade);
    });

    $query->endereco->update(['unidade_id' => $query->unidade]);

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
