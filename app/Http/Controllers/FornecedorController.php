<?php

namespace App\Http\Controllers;

use App\Enums\FornecedorArea;
use App\Http\Requests\FornecedorUpdateRequest;
use App\Models\Fornecedor;
use App\Models\FornecedorArea as FornecedorAreaModel;
use App\Models\Pessoa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    public function index(): View
    {
        return view('painel.fornecedores.index');
    }

    /**
     * Adiciona fornecedores na base
     *
     **/
    public function create(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'pessoa_uid' => ['required', 'string', 'exists:pessoas,uid'],
            ],
            [
                'pessoa_uid.required' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
                'pessoa_uid.string' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
                'pessoa_uid.exists' => 'Dados inválidos, seleciona uma pessoa e envie novamente',
            ]
        );

        $pessoa = Pessoa::select('id')->where('uid', $request->pessoa_uid)->first();

        // cria um fornecedor vinculado a pessoa
        $fornecedor = Fornecedor::create([
            'pessoa_id' => $pessoa->id,
        ]);

        if (! $fornecedor) {
            return redirect()->back()
                ->with('fornecedor-error', 'Ocorreu um erro! Revise os dados e tente novamente');
        }

        return redirect()->route('fornecedor-insert', $fornecedor->uid)
            ->with('success', 'Fornecedor cadastrado com sucesso');
    }

    /**
     * Tela de edição de usuário
     *
     **/
    public function insert(Fornecedor $fornecedor): View
    {
        $fornecedor->load('pessoa', 'areas');

        return view('painel.fornecedores.edit', ['fornecedor' => $fornecedor]);
    }

    /**
     * Edita dados de usuário
     *
     **/
    public function update(FornecedorUpdateRequest $request, Fornecedor $fornecedor): RedirectResponse
    {

        DB::transaction(function () use ($request, $fornecedor): void {
            $fornecedor->update([
                'fornecedor_desde' => $request->fornecedor_desde,
                'ativo' => (int) $request->ativo,
                'observacoes' => $request->observacoes,
            ]);

            $fornecedor->areas()->delete();

            $validAreas = array_column(FornecedorArea::cases(), 'value');
            foreach ($request->input('areas', []) as $index => $areaValue) {
                if (! $areaValue || ! in_array($areaValue, $validAreas)) {
                    continue;
                }
                $data = $request->input("areas_data.{$areaValue}", []);
                FornecedorAreaModel::create([
                    'fornecedor_id' => $fornecedor->id,
                    'area' => $areaValue,
                    'atuacao' => $data['atuacao'] ?? null,
                    'pessoa_contato' => $data['pessoa_contato'] ?? null,
                    'pessoa_contato_email' => $data['pessoa_contato_email'] ?? null,
                    'pessoa_contato_telefone' => $data['pessoa_contato_telefone'] ?? null,
                ]);
            }

        });

        return redirect()->back()->with('success', 'Fornecedor atualizado com sucesso');
    }

    /**
     * Remove usuário
     *
     **/
    public function delete(Fornecedor $fornecedor): RedirectResponse
    {
        $fornecedor->delete();

        return redirect()->route('fornecedor-index')->with('warning', 'Fornecedor removido');
    }
}
