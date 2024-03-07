<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoAvaliacao;

class TipoAvaliacaoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {

    return view('painel.tipos-avaliacao.index', ['avaliacoes' => TipoAvaliacao::all()]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',

      ]
    );

    $validated['uid'] = config('hashing.uid');

    $tipo_avaliacao = TipoAvaliacao::create($validated);

    if (!$tipo_avaliacao) {
      return redirect()->back()
        ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('tipo-avaliacao-index')
      ->with('success', 'Tipo Avaliação cadastrado com sucesso');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, TipoAvaliacao $tipoAvaliacao)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
      ]
    );

    $tipoAvaliacao->update($validated);

    return redirect()->route('tipo-avaliacao-index')
      ->with('success', 'Tipo Avaliação cadastrado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(TipoAvaliacao $tipoAvaliacao)
  {
    $tipoAvaliacao->delete();

    return redirect()->route('tipo-avaliacao-index')
      ->with('success', 'Tipo Avaliação removido com sucesso');
  }
}
