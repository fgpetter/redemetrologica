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
  public function create(Request $request)
  {
    $request->validate(
      [
        'nome' => ['required', 'string', 'max:255'],
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
      'name' => $request->get('nome'),
      'email' => $request->get('email'),
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
    return view('painel.users.user-update', ['user' => $user]);
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
    $request->validate(
      [
        'nome' => ['required', 'string', 'max:255'],
        'email' => ['unique:users,email,' . $user->id, 'required', 'string', 'email'],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'],
      ],
      [
        'nome.required' => 'Preencha o campo nome',
        'email.required' => 'Preencha o campo email',
        'email.email' => 'Não é um email válido',
        'email.unique' => 'Esse email já está em uso',
        'password.confirmed' => 'As senhas não conferem',
        'password.min' => 'A senha deve ter pelo menos 8 caracteres',
      ]
    );

    $user->update([
      'name' => $request->get('nome'),
      'email' => $request->get('email')
    ]);

    if($request->get('password')) {
      $user->update([
        'password' => Hash::make($request->get('password'))
      ]);
    }

    if (!$user) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }
    return redirect()->route('user-index')->with('success', 'Usuário atualizado');
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
}
