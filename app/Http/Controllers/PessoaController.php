<?php

namespace App\Http\Controllers;

use App\Actions\CreateUserForPessoaAction;
use App\Http\Requests\PessoaCreateRequest;
use App\Http\Requests\PessoaUpdateRequest;
use App\Models\Laboratorio;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PessoaController extends Controller
{
    /**
     *Gera pagina de listagem de usuários
     */
    public function index(Request $request): View
    {
        $name = in_array($request->name, ['asc', 'desc', 'ASC', 'DESC'], true) ? $request->name : null;
        $data = in_array($request->data, ['asc', 'desc', 'ASC', 'DESC'], true) ? $request->data : null;
        $doc = in_array($request->doc, ['asc', 'desc', 'ASC', 'DESC'], true) ? $request->doc : null;
        $busca_nome = $request->buscanome;
        $associado = $request->associado;
        $busca_doc = preg_replace('/[^0-9]/', '', $request->buscadoc);

        $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj', 'created_at', 'associado', 'tipo_pessoa')
            ->addSelect(['laboratorio_uid' => Laboratorio::select('uid')
                ->whereColumn('pessoa_id', 'pessoas.id')->orderBy('id')->limit(1)])
            ->when($name, function (Builder $query, $name) {
                $query->orderBy('nome_razao', $name);
            })
            ->when($data, function (Builder $query, $data) {
                $query->orderBy('created_at', $data);
            })
            ->when($doc, function (Builder $query, $doc) {
                $query->orderBy('cpf_cnpj', $doc);
            })
            ->when($associado == 'ASC', function (Builder $query) {
                $query->where('associado', 1);
            })
            ->when($busca_nome, function (Builder $query, $busca_nome) {
                $query->where('nome_razao', 'LIKE', "%$busca_nome%");
            })
            ->when($busca_doc, function (Builder $query, $busca_doc) {
                $query->where('cpf_cnpj', 'LIKE', "%$busca_doc%");
            })
            ->paginate(10);

        return view('painel.pessoas.index', ['pessoas' => $pessoas]);
    }

    /**
     * Adiciona pessoa
     *
     **/
    public function create(PessoaCreateRequest $request): RedirectResponse
    {

        $pessoa = Pessoa::create($request->validated());

        if (! $pessoa) {
            return redirect()->back()->with('error', 'Ocorreu um erro!');
        }

        $uid = Pessoa::find($pessoa->id)->uid;

        return redirect()->route('pessoa-insert', ['pessoa' => $uid])->with('success', 'Pessoa cadastrada com sucesso');
    }

    /**
     * Tela de edição de pessoa
     *
     **/
    public function insert(Pessoa $pessoa): View
    {
        $empresas = Pessoa::where('tipo_pessoa', 'PJ')->whereNotIn('id', $pessoa->empresas->pluck('id'))->get();

        return view('painel.pessoas.edit', [
            'pessoa' => $pessoa,
            'empresas' => $empresas,
        ]);
    }

    /**
     * Edita dados de pessoa
     *
     **/
    public function update(PessoaUpdateRequest $request, Pessoa $pessoa): RedirectResponse
    {

        $pessoa->update($request->validated());

        if ($pessoa->user()) {
            $pessoa->user()->update([
                'name' => $request->safe()->nome_razao,
            ]);
        }

        return back()->with('success', 'Pessoa atualizada com sucesso');
    }

    /**
     * Remove usuário
     *
     * @param  User  $user
     **/
    public function delete(Pessoa $pessoa): RedirectResponse
    {
        $pessoa->user()->delete(); // remove usuário associado
        $pessoa->delete(); // soft delete

        return redirect()->route('pessoa-index')->with('warning', 'Pessoa removida');
    }

    /**
     * Associa uma empresa a uma pessoa
     */
    public function associaEmpresa(Request $request, Pessoa $pessoa): RedirectResponse
    {
        $empresa = Pessoa::where('uid', $request->empresa_id)->first();

        if ($request->get('detach')) {
            $pessoa->empresas()->detach($empresa->id);

        } else {
            $pessoa->empresas()->attach($empresa->id);
        }

        return back()->with('success', 'Pessoa atualizada com sucesso');
    }

    /**
     * Associa um usuário a uma pessoa
     */
    public function associaUsuario(Pessoa $pessoa): RedirectResponse
    {
        if (blank($pessoa->email)) {
            Log::channel('validation')->info('Erro de validação', [
                'user' => auth()->user() ?? null,
                'uri' => request()->fullUrl() ?? null,
                'method' => get_class($this).'::'.__FUNCTION__,
                'errors' => ['email' => 'É necessário preencher o e-mail da pessoa antes de criar o usuário.'],
            ]);

            return back()->with('error', 'É necessário preencher o e-mail da pessoa antes de criar o usuário.');
        }

        CreateUserForPessoaAction::handle($pessoa);

        return back()->with('success', 'Pessoa atualizada com sucesso');
    }
}
