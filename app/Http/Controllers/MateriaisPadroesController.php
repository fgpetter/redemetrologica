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
        'cod_fabricante' => ['nullable', 'string'],
        'fabricante' => ['nullable', 'string'],
        'fornecedor' => ['nullable', 'string'],
        'marca' => ['nullable', 'string'],
        'tipo' => ['required', Rule::in(['CURSOS', 'INTERLAB', 'AMBOS'])],
        'padrao' => ['nullable', 'integer'],
        'valor' => ['nullable', 'string'],
        'tipo_despesa' => ['required', Rule::in(['FIXO', 'VARIAVEL', 'OUTROS'])],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
        'descricao.string' => 'Conteúdo inválido',
        'cod_fabricante.string' => 'Conteúdo inválido',
        'fabricante.string' =>  'Conteúdo inválido',
        'fornecedor.string' =>  'Conteúdo inválido',
        'marca.string' =>  'Conteúdo inválido',
        'tipo.required' => 'Selecione uma opção',
        'tipo.in' => 'Opção Inválida',
        'valor.string' => 'Conteúdo inválido',
        'tipo_despesa.required' => 'Selecione uma opção',
        'tipo_despesa.in' => 'Opção Inválida',
        'observacoes.string' => 'Conteúdo inválido',

      ]
    );

    $validated['uid'] = config('hashing.uid');
    ($validated['valor']) ? $validated['valor'] = str_replace(',', '.', $validated['valor']) : null;

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
        'cod_fabricante' => ['nullable', 'string'],
        'fabricante' => ['nullable', 'string'],
        'fornecedor' => ['nullable', 'string'],
        'marca' => ['nullable', 'string'],
        'tipo' => ['required', Rule::in(['CURSOS', 'INTERLAB', 'AMBOS'])],
        'padrao' => ['nullable', 'integer'],
        'valor' => ['nullable', 'string'],
        'tipo_despesa' => ['required', Rule::in(['FIXO', 'VARIAVEL', 'OUTROS'])],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
        'descricao.string' => 'Conteúdo inválido',
        'cod_fabricante.string' => 'Conteúdo inválido',
        'fabricante.string' =>  'Conteúdo inválido',
        'fornecedor.string' =>  'Conteúdo inválido',
        'marca.string' =>  'Conteúdo inválido',
        'tipo.required' => 'Selecione uma opção',
        'tipo.in' => 'Opção Inválida',
        'valor.string' => 'Conteúdo inválido',
        'tipo_despesa.required' => 'Selecione uma opção',
        'tipo_despesa.in' => 'Opção Inválida',
        'observacoes.string' => 'Conteúdo inválido',
        'padrao.integer' => 'Opção Inválida'

      ]
    );

    ($validated['valor']) ? $validated['valor'] = str_replace(',', '.', $validated['valor']) : null;
    $validated['padrao'] = ($request->has('padrao')) ? 1 : 0;

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
      ->with('success', 'Material Padrão removido com sucesso');
  }
}
