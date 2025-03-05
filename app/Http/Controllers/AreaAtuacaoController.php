<?php

namespace App\Http\Controllers;

use App\Models\AreaAtuacao;
use Illuminate\Http\Request;

class AreaAtuacaoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('painel.area-atuacao.index', ['areas_atuacao' => AreaAtuacao::all()]);
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
        'observacoes.string' => 'Conteúdo inválido',

      ]
    );


    $material_padrao = AreaAtuacao::create($validated);

    if (!$material_padrao) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('area-atuacao-index')
      ->with('success', 'Área de atuação cadastrada com sucesso');
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, AreaAtuacao $areaAtuacao)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
        'observacoes' => ['nullable', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
        'observacoes.string' => 'Conteúdo inválido',

      ]
    );

    $areaAtuacao->update($validated);

    return redirect()->route('area-atuacao-index')
      ->with('success', 'Área de atuação atualizado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(AreaAtuacao $areaAtuacao)
  {
    $areaAtuacao->delete();

    return redirect()->route('area-atuacao-index')
      ->with('warning', 'Área de atuação removida com sucesso');
  }
}
