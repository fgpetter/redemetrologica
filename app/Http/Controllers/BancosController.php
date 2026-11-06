<?php

namespace App\Http\Controllers;

use App\Http\Requests\BancoRequest;
use App\Models\Banco;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BancosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('painel.bancos.index', ['bancos' => Banco::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(BancoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $banco = Banco::create($validated);

        if (! $banco) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('banco-index')
            ->with('success', 'Banco cadastrado com sucesso');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BancoRequest $request, Banco $banco): RedirectResponse
    {
        $validated = $request->validated();

        $validated['padrao'] = ($request->has('padrao')) ? 1 : 0;
        $validated['movimenta_financeiro'] = ($request->has('padrao')) ? 1 : 0;

        $banco->update($validated);

        return redirect()->route('banco-index')
            ->with('success', 'Banco atualizado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Banco $banco): RedirectResponse
    {
        $banco->delete();

        return redirect()->route('banco-index')
            ->with('warning', 'Banco removido com sucesso');
    }
}
