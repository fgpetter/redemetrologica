<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use App\Models\PlanoConta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;


class PlanoContaController extends Controller
{
  /**
   * Display a listing of the resource.
   * @return View
   */
  public function index(Request $request): View
{
    $sortDirection = $request->input('order', 'asc');
    $sortField     = $request->input('orderBy', 'descricao');
    
    $buscadecricao       = $request->input('buscadecricao');
    $buscacodcontabil    = $request->input('buscacodcontabil');
    $buscagrupocontas    = $request->input('buscagrupocontas');
    $buscacentrocusto    = $request->input('buscacentrocusto');

    $planocontas = PlanoConta::with(['centrocusto' => function ($query) {
            $query->withTrashed();
        }])
        ->when($buscadecricao, function ($query) use ($buscadecricao) {
            $query->where('descricao', 'LIKE', "%{$buscadecricao}%");
        })
        ->when($buscacodcontabil, function ($query) use ($buscacodcontabil) {
            $query->where('codigo_contabil', 'LIKE', "%{$buscacodcontabil}%");
        })
        ->when($buscagrupocontas, function ($query) use ($buscagrupocontas) {
            $query->where('grupo_contas', 'LIKE', "%{$buscagrupocontas}%");
        })
        ->when($buscacentrocusto, function ($query) use ($buscacentrocusto) {
            $query->whereHas('centrocusto', function ($q) use ($buscacentrocusto) {
                $q->where('descricao', 'LIKE', "%{$buscacentrocusto}%");
            });
        })
        ->when($sortField == 'centrocusto', function ($query) use ($sortDirection) {
            $query->leftJoin('centro_custos', 'plano_contas.centro_custo_id', '=', 'centro_custos.id')
                  ->orderBy('centro_custos.descricao', $sortDirection)
                  ->addSelect('plano_contas.*');
        })
        ->when(in_array($sortField, ['descricao', 'codigo_contabil', 'grupo_contas']), function ($query) use ($sortField, $sortDirection) {
            $query->orderBy($sortField, $sortDirection);
        })
        ->paginate(10)
        ->withQueryString();

    $centrocustos = CentroCusto::all();

    return view('painel.plano-conta.index', [
        'planocontas'  => $planocontas,
        'centrocustos' => $centrocustos,
    ]);
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
