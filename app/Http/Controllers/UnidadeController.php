<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\Unidade;
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
    $validator = Validator::make( $request->all(), [
        'nome' => ['required', 'string', 'max:191'],
        'cnpj' => ['required', 'cnpj'],
        'telefone' => ['required', 'celular_com_ddd'],
        'email' => ['required', 'email'],
        'nome_responsavel' => ['required', 'string'],
        'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
        'cep' => ['required', 'formato_cep'],
        'endereco' => ['required', 'string'],
        'complemento' => ['nullable', 'string'],
        'cidade' => ['required', 'string'],
        'bairro' => ['required', 'string'],
        'uf' => ['required', 'string'],
      ], [
        'nome.required' => 'Preencha o campo nome ou razão social',
        'nome.string' => 'O campo nome ou razão é inválido',
        'cnpj.required' => 'Preencha o campo CNPJ',
        'cnpj.cnpj' => 'O campo CNPJ deve ser um número de CNPJ válido',
        'telefone.required' => 'Preencha o campo telefone',
        'telefone.celular_com_ddd' => 'O campo telefone deve ser um número de telefone válido',
        'email.required' => 'Preencha o campo email',
        'email.email' => 'O campo email deve ser um email válido',
        'nome_responsavel.required' => 'Preencha o campo nome do responsável',
        'nome_responsavel.string' => 'O campo nome do responsável é inválido',
        'nome.max' => 'O campo nome ou razão social é muito longo',
        'cnpj.required' => 'Preencha o campo CNPJ',
        'cnpj.cnpj' => 'O campo CNPJ deve ser um número de CNPJ válido',
        'telefone.required' => 'Preencha o campo telefone',
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

    DB::transaction(function () use ($prepared_data) {

      $pessoa = Pessoa::where('uid', $prepared_data['pessoa_uid'])->first();

      $unidade = Unidade::create([
        'pessoa_id' => $pessoa->id,
        'nome' => strtoupper($prepared_data['nome']),
        'cnpj' => return_only_nunbers($prepared_data['cnpj']) ?? null,
        'telefone' => return_only_nunbers($prepared_data['telefone']) ?? null,
        'email' => $prepared_data['email'] ?? null,
        'nome_responsavel' => $prepared_data['nome_responsavel'] ?? null,
      ]);

      $pessoa->enderecos()->create([
        'unidade_id' => $unidade->id,
        'cep' => return_only_nunbers($prepared_data['cep']),
        'endereco' => $prepared_data['endereco'],
        'complemento' => $prepared_data['complemento'] ?? null,
        'bairro' => $prepared_data['bairro'],
        'cidade' => $prepared_data['cidade'],
        'uf' => $prepared_data['uf'],
      ]);

      if (!$unidade) {
        return redirect()->back()->with('error', 'Ocorreu um erro!');
      }

    });


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
    $validator = Validator::make( $request->all(), [
      'nome' => ['required', 'string', 'max:191'],
      'cnpj' => ['required', 'cnpj'],
      'telefone' => ['required', 'celular_com_ddd'],
      'email' => ['required', 'email'],
      'nome_responsavel' => ['required', 'string'],
      'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
      'cep' => ['required', 'formato_cep'],
      'endereco' => ['required', 'string'],
      'complemento' => ['nullable', 'string'],
      'cidade' => ['required', 'string'],
      'bairro' => ['required', 'string'],
      'uf' => ['required', 'string'],
      ], [
        'nome.required' => 'Preencha o campo nome ou razão social',
        'nome.string' => 'O campo nome ou razão é inválido',
        'cnpj.required' => 'Preencha o campo CNPJ',
        'cnpj.cnpj' => 'O campo CNPJ deve ser um número de CNPJ válido',
        'telefone.required' => 'Preencha o campo telefone',
        'telefone.celular_com_ddd' => 'O campo telefone deve ser um número de telefone válido',
        'email.required' => 'Preencha o campo email',
        'email.email' => 'O campo email deve ser um email válido',
        'nome_responsavel.required' => 'Preencha o campo nome do responsável',
        'nome_responsavel.string' => 'O campo nome do responsável é inválido',
        'nome.max' => 'O campo nome ou razão social é muito longo',
        'cnpj.required' => 'Preencha o campo CNPJ',
        'cnpj.cnpj' => 'O campo CNPJ deve ser um número de CNPJ válido',
        'telefone.required' => 'Preencha o campo telefone',
      ]
    );

    if ($validator->fails()) {
      Log::channel('validation')->info(
        "Erro de validação (Update Unidade)",
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
        ->with('error', 'Dados inválidos')
        ->withErrors($validator);
    }

    $prepared_data = $validator->validated();

    DB::transaction(function () use ($prepared_data, $unidade, $request) {

      $pessoa = Pessoa::where('uid', $prepared_data['pessoa_uid'])->first();
      $pessoa->unidades()->where('id', $unidade->id)->update([
        'nome' => strtoupper($prepared_data['nome']),
        'cnpj' => return_only_nunbers($prepared_data['cnpj']) ?? null,
        'telefone' => return_only_nunbers($prepared_data['telefone']) ?? null,
        'email' => $prepared_data['email'] ?? null,
        'nome_responsavel' => $prepared_data['nome_responsavel'] ?? null,
      ]);

      $pessoa->enderecos()->where('unidade_id', $unidade->id)->update([
        'cep' => return_only_nunbers($prepared_data['cep']),
        'endereco' => $prepared_data['endereco'],
        'complemento' => $prepared_data['complemento'] ?? null,
        'bairro' => $prepared_data['bairro'],
        'cidade' => $prepared_data['cidade'],
        'uf' => $prepared_data['uf'],
      ]);
    });

    return redirect()->back()->with('success', 'Unidade cadastrada com sucesso');
  }

  /**
   * Remove unidade
   *
   * @param Unidade $user
   * @return RedirectResponse
   **/
  public function delete(Unidade $unidade): RedirectResponse
  {
    $unidade->endereco()->delete();
    $unidade->delete();

    return redirect()->back()->with('warning', 'Unidade removida');
  }
}