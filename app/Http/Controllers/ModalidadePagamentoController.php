<?php

namespace App\Http\Controllers;

use App\Models\ModalidadePagamento;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModalidadePagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {

        return view('painel.modalidade-pagamento.index', ['modalidadepagamentos' => ModalidadePagamento::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'descricao' => ['required', 'string'],
            ],
            [
                'descricao.required' => 'Preencha o campo Descrição',

            ]
        );

        $modalidade_pagamento = ModalidadePagamento::create($validated);

        if (! $modalidade_pagamento) {
            return redirect()->back()
                ->with('modalidade-pagamento-error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('modalidade-pagamento-index')
            ->with('success', 'Centro de Custo cadastrado com sucesso');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModalidadePagamento $modalidadePagamento): RedirectResponse
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
    public function delete(ModalidadePagamento $modalidadePagamento): RedirectResponse
    {
        $modalidadePagamento->delete();

        return redirect()->route('modalidade-pagamento-index')
            ->with('warning', 'Centro de Custo removido com sucesso');
    }
}
