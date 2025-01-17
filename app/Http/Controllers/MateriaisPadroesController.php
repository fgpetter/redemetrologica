<?php

namespace App\Http\Controllers;

use App\Models\MaterialPadrao;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;


class MateriaisPadroesController extends Controller
{
  /**
   * Display a listing of the resource.
   * @return View
   */
  public function index(): View
  {
    return view('painel.materiais-padroes.index', ['materiais' => MaterialPadrao::paginate(10)]);
  }

  /**
   * Store a newly created resource in storage.
   *    
   * @param Request $request
   * @return RedirectResponse
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
        'tipo' => ['required', Rule::in(['CURSOS', 'INTERLAB', 'AMBOS'])],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
        'descricao.string' => 'Conteúdo inválido',
        'tipo.required' => 'Selecione uma opção',
        'tipo.in' => 'Opção Inválida',
        'observacoes.string' => 'Conteúdo inválido',

      ]
    );

    ($validated['valor']) ? $validated['valor'] = formataMoeda($validated['valor']) : null;

    $material_padrao = MaterialPadrao::create($validated);

    if (!$material_padrao) {
      return redirect()->back()
        ->with('material-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('materiais-padroes-index')
      ->with('success', 'Material Padrão cadastrado com sucesso');
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, MaterialPadrao $materiaisPadroes)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
        'tipo' => ['required', Rule::in(['CURSOS', 'INTERLAB', 'AMBOS'])],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
        'descricao.string' => 'Conteúdo inválido',
        'tipo.required' => 'Selecione uma opção',
        'tipo.in' => 'Opção Inválida',
        'observacoes.string' => 'Conteúdo inválido',

      ]
    );

    ($validated['valor']) ? $validated['valor'] = formataMoeda($validated['valor']) : null;

    $materiaisPadroes->update($validated);

    return redirect()->route('materiais-padroes-index')
      ->with('success', 'Material Padrão atualizado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(MaterialPadrao $materiaisPadroes)
  {
    $materiaisPadroes->delete();

    return redirect()->route('materiais-padroes-index')
      ->with('warning', 'Material Padrão removido com sucesso');
  }

  /**
   * Formata valor para decimal padrão SQL
   *
   * @param string $valor
   * @return string|null
   */
  private function formataMoeda($valor): ?string
  {
    if ($valor) {
      if(str_contains($valor, '.') && str_contains($valor, ',') ) {
        return str_replace(',', '.', str_replace('.', '', $valor));
      }

      if(str_contains($valor, '.') && !str_contains($valor, ',') ) {
        return $valor;
      }

      if(str_contains($valor, ',') && !str_contains($valor, '.') ){
        return str_replace(',', '.', $valor);
      }

    } else {
      return null;
    }
  }

}
