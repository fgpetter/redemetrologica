<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
  public function impersonate(Request $request)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id'
    ]);

    $user = User::findOrFail($request->user_id);

    // Armazena o ID do admin original
    session(['impersonator_id' => auth()->id()]);

    // Faz login como o usuário selecionado
    auth()->login($user);

    return back()->with('success', 'Agora você está atuando como ' . ($user->name ?? $user->email));
  }

  public function stop()
  {
    // Recupera o admin original
    $originalId = session('impersonator_id');

    if ($originalId) {
      $originalUser = User::findOrFail($originalId);
      auth()->login($originalUser);
      session()->forget('impersonator_id');

      return back()->with('success', 'Personificação finalizada');
    }

    return back();
  }
} 