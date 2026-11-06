<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Gera pagina de listagem de usuários
     *
     **/
    public function index(Request $request): View
    {
        $order = $request->input('name', 'asc');
        $order = in_array($order, ['asc', 'desc', 'ASC', 'DESC'], true) ? $order : 'asc';
        $busca = $request->input('busca');

        $users = User::with(['permissions', 'pessoa'])
            ->when($busca, function ($query) use ($busca) {
                $query->where('name', 'LIKE', "%{$busca}%");
                $query->orWhere('email', 'LIKE', "%{$busca}%");
            })
            ->orderBy('name', $order)
            ->paginate(10)
            ->withQueryString();

        return view('painel.users.index', ['users' => $users]);
    }

    /**
     * Adiciona usuários na base
     *
     **/
    public function create(Request $request): RedirectResponse
    {
        $request->merge([
            'nome' => ucwords(strtolower($request->get('nome'))),
            'email' => strtolower($request->get('email')),
        ]);

        $validator = Validator::make($request->all(), [
            'nome' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'unique:users'],
        ], [
            'nome.required' => 'Preencha o campo nome',
            'email.required' => 'Preencha o campo email',
            'email.email' => 'Não é um email válido',
            'email.unique' => 'Esse email já está em uso',
        ]
        );

        if ($validator->fails()) {

            Log::channel('validation')->info('Erro de validação', [
                'user' => auth()->user() ?? null,
                'request' => $request->all() ?? null,
                'uri' => request()->fullUrl() ?? null,
                'method' => get_class($this).'::'.__FUNCTION__,
                'errors' => $validator->errors() ?? null,
            ]);

            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
        }

        $user = User::create([
            'name' => $validator->safe()->nome,
            'email' => $validator->safe()->email,
            'password' => Hash::make('Password'),
        ]);

        if (! $user) {
            return redirect()->back()->with('error', 'Ocorreu um erro!');
        }

        return redirect()->route('user-edit', ['user' => $user]);
    }

    /**
     * Tela de edição de usuário
     *
     **/
    public function view(User $user): View
    {
        if (auth()->user()->hasPermissionTo(['admin', 'funcionario']) || ($user->id == auth()->user()->id)) {
            $permissions = $user->permissions()->pluck('permission')->toArray();
            $endereco = $user->pessoa->enderecos()->first();

            return view('painel.users.edit', ['user' => $user, 'permissions' => $permissions, 'endereco' => $endereco]);
        }
        abort(404);
    }

    /**
     * Edita dados de usuário
     *
     **/
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        if (auth()->user()->hasPermissionTo(['admin', 'funcionario']) || ($user->id == auth()->user()->id)) {

            $user->update([
                'name' => ucwords($request->nome ?? ''),
                'email' => strtolower($request->email ?? ''),
            ]);

            if ($request->password) {
                $user->update([
                    'password' => Hash::make($request->password),
                    'temporary_password' => 0,
                ]);
            }

            // se cliente atualizar seus dados, atualiza também dados de pessoa e endereço
            if ($request->update_pessoa == 1) {
                $user->pessoa()->update([
                    'nome_razao' => ucwords($request->nome ?? ''),
                    'email' => strtolower($request->email ?? ''),
                    'cpf_cnpj' => $request->cpf_cnpj,
                    'telefone' => $request->telefone,
                    'telefone_alt' => $request->telefone_alt,
                    'celular' => $request->celular,
                ]);

                $user->pessoa->enderecos()->update([
                    'cep' => $request->cep,
                    'endereco' => $request->endereco,
                    'cidade' => $request->cidade,
                    'uf' => $request->uf,
                ]);
            } else { // mantem a consistencia dos dados pessoa -> usuario
                $user->pessoa()->update([
                    'nome_razao' => ucwords($request->nome ?? ''),
                    'email' => strtolower($request->email ?? ''),
                ]);
            }

            if (auth()->user()->hasPermissionTo(['admin', 'funcionario'])) {
                return redirect()->route('user-index')->with('success', 'Usuário atualizado');
            }

            return redirect()->route('painel-index')->with('success', 'Usuário atualizado');
        }

        abort(403);

    }

    /**
     * Remove usuário
     *
     **/
    public function delete(Request $request, User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('user-index')->with('warning', 'Usuário removido');
    }

    public function updatePermission(User $user, Request $request): RedirectResponse
    {
        $request->validate([
            'permission' => ['nullable', 'array'],
            'permission.*' => ['nullable', 'numeric'],
        ],
            [
                'permission.*.exists' => 'Permissão inválida',
            ]);
        $user->permissions()->sync($request->get('permission'));

        return redirect()->back()->with('success', 'Permissões atualizadas');
    }
}
