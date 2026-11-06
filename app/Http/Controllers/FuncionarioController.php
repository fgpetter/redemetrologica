<?php

namespace App\Http\Controllers;

use App\Actions\SalvarFuncionarioAction;
use App\Http\Requests\FuncionarioCreateRequest;
use App\Http\Requests\FuncionarioUpdateRequest;
use App\Models\Funcionario;
use App\Models\Pessoa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

class FuncionarioController extends Controller
{
    /**
     * Gera pagina de listagem de usuários
     *
     **/
    public function index(): View
    {
        $funcionarios = Funcionario::all();

        return view('painel.funcionarios.index', ['funcionarios' => $funcionarios]);
    }

    /**
     * Adiciona usuários na base
     *
     **/
    public function create(FuncionarioCreateRequest $request, SalvarFuncionarioAction $action): RedirectResponse
    {
        $action->create($request->validated());

        return redirect()->route('funcionario-index')
            ->with('funcionario-success', 'Funcionario cadastrada com sucesso');
    }

    /**
     * Tela de edição de usuário
     *
     **/
    public function insert(Funcionario $funcionario): View
    {
        return view('painel.funcionarios.edit', ['funcionario' => $funcionario]);
    }

    /**
     * Edita dados de usuário
     *
     * @param  Funcionario  $user
     **/
    public function update(FuncionarioUpdateRequest $request, Funcionario $funcionario, SalvarFuncionarioAction $action): RedirectResponse
    {
        $action->update($funcionario, $request->validated());

        return redirect()->back()->with('funcionario-success', 'Funcionario atualizado com sucesso');
    }

    /**
     * Remove usuário
     *
     * @param  User  $user
     **/
    public function delete(Funcionario $funcionario): RedirectResponse
    {
        if (File::exists(public_path($funcionario->curriculo))) {
            File::delete(public_path($funcionario->curriculo));
        }

        Pessoa::where('id', $funcionario->pessoa_id)->delete();

        $funcionario->delete();

        return redirect()->route('funcionario-index')->with('warning', 'Funcionario removido');
    }

    /**
     * Remove arquivo de curriculo
     *
     * @param  User  $user
     **/
    public function curriculoDelete(Funcionario $funcionario): RedirectResponse
    {
        // deleta arquivo anterior
        if (File::exists(public_path($funcionario->curriculo))) {
            File::delete(public_path($funcionario->curriculo));
        }

        $funcionario->update(['curriculo' => null]);

        return redirect()->back()->with('funcionario-success', 'Curriculo removido');
    }
}
