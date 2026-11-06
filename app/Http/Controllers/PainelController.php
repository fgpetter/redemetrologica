<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PainelController extends Controller
{
    public function index(): RedirectResponse|View
    {
        if (auth()->user()->temporary_password == true) {
            return redirect()->route('user-edit', auth()->user())
                ->with('password-error', true);
        }

        return view('index');
    }
}
