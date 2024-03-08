<?php

namespace App\Http\Controllers;

use App\Models\ModalidadePagamento;
use Illuminate\Http\Request;

class ModalidadePagamentoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {

    return view('painel.modalidade-pagamento.index', ['modalidadepagamentos' => ModalidadePagamento::all()]);
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

    $modalidade_pagamento = ModalidadePagamento::create($validated);

    if (!$modalidade_pagamento) {
      return redirect()->back()
        ->with('modalidade-pagamento-error', 'Ocorreu um erro! Revise os dados e tente novamente');
    }

    return redirect()->route('modalidade-pagamento-index')
      ->with('success', 'Centro de Custo cadastrado com sucesso');
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, ModalidadePagamento $modalidadePagamento)
  {
    $validated = $request->validate(
      [
        'descricao' => ['required', 'string'],
      ],
      [
        'descricao.required' => 'Preencha o campo Descrição',
      ]
    );

    $modalidadePagamento->update($validated);

    return redirect()->route('modalidade-pagamento-index')
      ->with('success', 'Centro de Custo cadastrado com sucesso');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(ModalidadePagamento $modalidadePagamento)
  {
    $modalidadePagamento->delete();

    return redirect()->route('modalidade-pagamento-index')
      ->with('warning', 'Centro de Custo removido com sucesso');
  }
}
