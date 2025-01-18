<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  /**
   * Gera pagina de listagem de usuários
   *
   * @return View
   **/
  public function index(): View
  {
    $users = User::with(['permissions','pessoa'])->paginate(10);
    return view('painel.users.user-index', ['users' => $users]);
  }

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request)
  {
    $request->validate(
      [
        'nome' => ['required', 'string', 'max:191'],
        'email' => ['unique:users', 'required', 'string', 'email'],
      ],
      [
        'nome.required' => 'Preencha o campo nome',
        'email.required' => 'Preencha o campo email',
        'email.email' => 'Não é um email válido',
        'email.unique' => 'Esse email já está em uso',
      ]
    );

    $user = User::create([
      'uid' => config('hashing.uid'),
      'name' => ucwords( $request->nome ?? '' ),
      'email' => strtolower( $request->email ?? '' ),
      'password' => Hash::make('Password')
    ]);

    if (!$user) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    return redirect()->route('user-edit', ['user' => $user]);
  }

  /**
   * Tela de edição de usuário
   *
   * @param User $user
   * @return View
   **/
  public function view(User $user): View
  {
    if( auth()->user()->hasPermissionTo(['admin', 'funcionario']) || ($user->id == auth()->user()->id) ) {
      $permissions = $user->permissions()->pluck('permission')->toArray();
      $endereco = $user->pessoa->enderecos()->first();
      return view('painel.users.user-update', ['user' => $user , 'permissions' => $permissions, 'endereco' => $endereco]);
    }
    abort(404);
  }

  /**
   * Edita dados de usuário
   *
   * @param Request $request
   * @param User $user
   * @return RedirectResponse
   **/
  public function update(Request $request, User $user): RedirectResponse
  {
    if( auth()->user()->hasPermissionTo(['admin', 'funcionario']) || ($user->id == auth()->user()->id) ) {
      $request->merge( return_only_nunbers( $request->only('cpf_cnpj','telefone','telefone_alt','celular') ) );
      
      $request->validate(
        [
          'nome' => ['required', 'string', 'max:191'],
          'email' => ['required', 'string', 'email','unique:users,email,'.$user->id],
          'password' => ['nullable', 'string', 'min:8', 'confirmed'],
          'cpf_cnpj' => ['required', 'cpf', 'unique:pessoas,cpf_cnpj,'.$user->pessoa->id],
          'telefone' => ['nullable', 'string', 'min:10', 'max:11'],
          'telefone_alt' => ['nullable', 'string', 'min:10', 'max:11'],
          'celular' => ['nullable', 'string', 'min:10', 'max:11'],
          'cep' => ['nullable', 'string'],
          'endereco' => ['nullable', 'string'],
          'cidade' => ['nullable', 'string'],
          'uf' => ['nullable', 'string'],
        ],[
          'nome.required' => 'Preencha o campo nome',
          'email.required' => 'Preencha o campo email',
          'email.email' => 'Não é um email válido',
          'email.unique' => 'Esse email já está em uso',
          'password.confirmed' => 'As senhas não conferem',
          'password.min' => 'A senha deve ter pelo menos 8 caracteres',
        ]
      );
  
      $user->update([
        'name' => ucwords( $request->nome ?? '' ),
        'email' => strtolower( $request->email ?? '')
      ]);
  
      if($request->password) {
        $user->update([
          'password' => Hash::make($request->password),
          'temporary_password' => 0
        ]);
      }

      // se cliente atualizar seus dados, atualiza também dados de pessoa e endereço
      if($request->update_pessoa == 1){
        $user->pessoa()->update([
          'nome_razao' => ucwords( $request->nome ?? '' ),
          'email' => strtolower( $request->email ?? ''),
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
          'nome_razao' => ucwords( $request->nome ?? '' ),
          'email' => strtolower( $request->email ?? ''),
        ]);
      }


      if(auth()->user()->hasPermissionTo(['admin', 'funcionario'])) {
        return redirect()->route('user-index')->with('success', 'Usuário atualizado');
      }
      return redirect()->route('painel-index')->with('success', 'Usuário atualizado');
    }

    abort(403);

  }

  /**
   * Remove usuário
   *
   * @param Request $request
   * @param User $user
   * @return RedirectResponse
   **/
  public function delete(Request $request, User $user): RedirectResponse
  {
    $user->delete();

    return redirect()->route('user-index')->with('warning', 'Usuário removido');;
  }

  public function updatePermission(User $user, Request $request)
  {
    $request->validate([
        'permission' => ['nullable', 'array'],
        'permission.*' => ['nullable','numeric'],
      ],
      [
        'permission.*.exists' => 'Permissão inválida',
      ]);
    $user->permissions()->sync($request->get('permission'));

    return redirect()->back()->with('success', 'Permissões atualizadas');
  }


}
