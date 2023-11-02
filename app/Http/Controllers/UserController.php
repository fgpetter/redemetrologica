<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    /**
     * Adiciona usuários na base
     *
     * @param Request $request
     * @return view
     **/
    public function create(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::create([
            'name' => $request->get('nome'),
            'email' => $request->get('email'),
            'password' => Hash::make('Password')
        ]);

        if ($user) {
            // Session::flash('message', 'Usuário adicionado com sucesso');
            // Session::flash('alert-class', 'alert-success');
            // return redirect()->back();
            dd($user);
        } else {
            dd('nok');
            // Session::flash('message', 'Algo errado ocorreu');
            // Session::flash('alert-class', 'alert-danger');
            // return redirect()->back();
        }

    }
}
