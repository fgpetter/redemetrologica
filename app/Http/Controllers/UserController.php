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
    $users = User::all();
    return view('painel.users.user-index', ['users' => $users]);
  }

  /**
   * Adiciona usuários na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    $request->validate([
      'nome' => ['required', 'string', 'max:255'],
      'email' => ['unique:users','required', 'string', 'email'],
    ],
    [
      'nome.required' => 'Preencha o campo nome',
      'email.required' => 'Preencha o campo email',
      'email.email' => 'Não é um email válido',
      'email.unique' => 'Esse email já está em uso',
    ]);

    $user = User::create([
      'uid' => substr(hrtime(true), -9, 9),
      'name' => $request->get('nome'),
      'email' => $request->get('email'),
      'password' => Hash::make('Password')
    ]);

    if(!$user){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
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
    return view('users.user-update', ['user' => $user]);
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
    $request->validate([
      'nome' => ['required', 'string', 'max:255'],
      'email' => ['unique:users,email,'.$user->id,'required', 'string', 'email'],
      ],[
      'nome.required' => 'Preencha o campo nome',
      'email.required' => 'Preencha o campo email',
      'email.email' => 'Não é um email válido',
      'email.unique' => 'Esse email já está em uso',
      ]
    );

    $user->update([
      'name' => $request->get('nome'),
      'email' => $request->get('email'),
      'password' => Hash::make('Password')
    ]);

    if(!$user){
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    return redirect()->route('user-index')->with('update-success', 'Usuário atualizado');
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

    return redirect()->route('user-index')->with('update-success', 'Usuário removido');;
  }

}
