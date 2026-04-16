<?php

namespace App\Http\Controllers;

use App\Enums\FornecedorArea;
use App\Models\Fornecedor;
use App\Models\FornecedorArea as FornecedorAreaModel;
use App\Models\Pessoa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FornecedorController extends Controller
{
  public function index(): View
  {
    return view('painel.fornecedores.index');
  }

  /**
   * Adiciona fornecedores na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate(
      [
        'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
      ],
      [
        'pessoa_uid.required' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.string' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
        'pessoa_uid.exists' => 'Dados inválidos, seleciona uma pessoa e envie novamente'
      ]
    );

    $pessoa = Pessoa::select('id')->where('uid', $request->pessoa_uid)->first();

    // cria um fornecedor vinculado a pessoa
    $fornecedor = Fornecedor::create([
      'pessoa_id' => $pessoa->id,
    ]);

    if (!$fornecedor) {
      return redirect()->back()
        ->with('fornecedor-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('fornecedor-insert', $fornecedor->uid)
      ->with('success', 'Fornecedor cadastrado com sucesso');
  }

  /**
   * Tela de edição de usuário
   *
   * @param Fornecedor $fornecedor
   * @return View
   **/
  public function insert(Fornecedor $fornecedor): View
  {
    $fornecedor->load('pessoa', 'areas');

    return view('painel.fornecedores.insert', ['fornecedor' => $fornecedor]);
  }

  /**
   * Edita dados de usuário
   *
   * @param Request $request
   * @param Fornecedor $fornecedor
   * @return RedirectResponse
   **/
  public function update(Request $request, Fornecedor $fornecedor): RedirectResponse
  {
    $validator = Validator::make($request->all(), [
        'fornecedor_desde' => ['nullable', 'date'],
        'ativo' => ['required', 'in:0,1'],
        'observacoes' => ['nullable', 'string', 'max:1000'],
        'areas' => ['nullable', 'array'],
        'areas.*' => ['in:'.implode(',', array_column(FornecedorArea::cases(), 'value'))],
        'areas_data' => ['nullable', 'array'],
        'areas_data.*.atuacao' => ['nullable', 'string', 'max:191'],
        'areas_data.*.pessoa_contato' => ['nullable', 'string', 'max:191'],
        'areas_data.*.pessoa_contato_email' => ['nullable', 'email', 'max:191'],
        'areas_data.*.pessoa_contato_telefone' => ['nullable', 'string', 'max:20'],
    ], [
        'fornecedor_desde.date' => 'Data de início inválida',
        'ativo.required' => 'Informe se o fornecedor está ativo',
        'ativo.in' => 'Valor de ativo inválido',
        'observacoes.max' => 'Observações inválidas',
        'areas.*.in' => 'Área inválida',
    ]);

    if ($validator->fails()) {
      Log::channel('validation')->info('Erro de validação', [
          'user' => auth()->user() ?? null,
          'request' => $request->all() ?? null,
          'uri' => request()->fullUrl() ?? null,
          'method' => get_class($this).'::'.__FUNCTION__,
          'errors' => $validator->errors() ?? null,
      ]);

      return back()
          ->withErrors($validator, 'principal')
          ->withInput()
          ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }

    $fornecedor->update([
        'fornecedor_desde' => $request->fornecedor_desde,
        'ativo' => (int) $request->ativo,
        'observacoes' => $request->observacoes,
    ]);

    $fornecedor->areas()->delete();

    $validAreas = array_column(FornecedorArea::cases(), 'value');
    foreach ($request->input('areas', []) as $index => $areaValue) {
      if (! $areaValue || ! in_array($areaValue, $validAreas)) {
        continue;
      }
      $data = $request->input("areas_data.{$areaValue}", []);
      FornecedorAreaModel::create([
          'fornecedor_id' => $fornecedor->id,
          'area' => $areaValue,
          'atuacao' => $data['atuacao'] ?? null,
          'pessoa_contato' => $data['pessoa_contato'] ?? null,
          'pessoa_contato_email' => $data['pessoa_contato_email'] ?? null,
          'pessoa_contato_telefone' => $data['pessoa_contato_telefone'] ?? null,
      ]);
    }

    return redirect()->back()->with('success', 'Fornecedor atualizado com sucesso');
  }

  /**
   * Remove usuário
   *
   * @param Fornecedor $fornecedor
   * @return RedirectResponse
   **/
  public function delete(Fornecedor $fornecedor): RedirectResponse
  {
    $fornecedor->delete();
    return redirect()->route('fornecedor-index')->with('warning', 'Fornecedor removido');
  }
}
