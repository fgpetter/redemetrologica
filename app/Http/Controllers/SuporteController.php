<?php

namespace App\Http\Controllers;

use App\Models\SuporteDemanda;
use Illuminate\Contracts\View\View;

class SuporteController extends Controller
{
    public function index(): View
    {
        return view('painel.suporte.index');
    }

    public function insert(?SuporteDemanda $demanda = null): View
    {
        return view('painel.suporte.insert', ['demanda' => $demanda]);
    }
}
