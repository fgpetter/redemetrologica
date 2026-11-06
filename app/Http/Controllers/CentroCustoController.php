<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use App\Models\LancamentoFinanceiro;
use App\Models\PlanoConta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CentroCustoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {

        return view('painel.centro-custo.index', ['centrocustos' => CentroCusto::all()]);
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

        $centro_custo = CentroCusto::create($validated);

        if (! $centro_custo) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('centro-custo-index')
            ->with('success', 'Centro de Custo cadastrado com sucesso');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CentroCusto $centroCusto): RedirectResponse
    {
        $validated = $request->validate(
            [
                'descricao' => ['required', 'string'],
            ],
            [
                'descricao.required' => 'Preencha o campo Descrição',
            ]
        );

        $centroCusto->update($validated);

        return redirect()->route('centro-custo-index')
            ->with('success', 'Centro de Custo cadastrado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(CentroCusto $centroCusto): RedirectResponse
    {
        $tem_lac_financ = LancamentoFinanceiro::where('centro_custo_id', $centroCusto->id)->first();
        $tem_plano_contas = PlanoConta::where('centro_custo_id', $centroCusto->id)->first();
        if ($tem_lac_financ || $tem_plano_contas) {
            $centroCusto->delete();
        } else {
            $centroCusto->forceDelete();
        }

        return redirect()->route('centro-custo-index')
            ->with('warning', 'Centro de Custo removido com sucesso');
    }
}
