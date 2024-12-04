<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PainelController extends Controller
{
    public function index()
    {
        if( auth()->user()->temporary_password == true ) {
            return redirect()->route('user-edit', auth()->user())
            ->with('password-error', true);
        }
        return view('index');
    }
}
