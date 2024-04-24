<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use App\Models\PlanoConta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;


class PlanoContaController extends Controller
{
  /**
   * Display a listing of the resource.
   * @return View
   */
  public function index(): View
  {
    $data = [
      'planocontas' => PlanoConta::with(['centrocusto' => function ($query) {
        $query->withTrashed();
      }])->paginate(10),

      'centrocustos' => CentroCusto::all()
    ];
    return view('painel.plano-conta.index', $data);
  }

  /**
   * Store a newly created resource in storage.
   *    
   * @param Request $request
   * @return RedirectResponse
   */
  public function store(Request $request): RedirectResponse
  {
    //dd($request);
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
        'centro_custo_id' => ['nullable', 'exists:centro_custos,id'],
        'codigo_contabil' => ['nullable', 'numeric', 'max_digits:19'],
        'grupo_contas' => ['nullable', 'string'],
      ],
      [
        'descricao.string' => 'Conteúdo inválido',
        'centro_custo_id.exists' => 'Opção inválida',
        'codigo_contabil.numeric' => 'Permitido somente números',
        'codigo_contabil.max_digits' => 'Tamanho máximo 19 dígitos',
        'grupo_contas.istring' => 'Conteúdo inválido',
      ]
    );

    $validated['uid'] = config('hashing.uid');

    $planoconta = PlanoConta::create($validated);

    if (!$planoconta) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('plano-conta-index')
      ->with('success', 'Plano de conta cadastrado com sucesso');
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, PlanoConta $planoconta)
  {
    $validated = $request->validate(
      [
        'descricao' => ['nullable', 'string'],
        'centro_custo_id' => ['nullable', 'exists:centro_custos,id'],
        'codigo_contabil' => ['nullable', 'numeric', 'max_digits:19'],
        'grupo_contas' => ['nullable', 'string'],
      ],
      [
        'descricao.string' => 'Conteúdo inválido',
        'centro_custo_id.exists' => 'Opção inválida',
        'codigo_contabil.numeric' => 'Permitido somente números',
        'codigo_contabil.max_digits' => 'Tamanho máximo 19 dígitos',
        'grupo_contas.istring' => 'Conteúdo inválido',
      ]
    );

    $planoconta->update($validated);

    return redirect()->route('plano-conta-index')
      ->with('success', 'Plano de conta atualizado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(PlanoConta $planoconta)
  {
    $planoconta->delete();

    return redirect()->route('plano-conta-index')
      ->with('warning', 'Plano de conta removido com sucesso');
  }
}
