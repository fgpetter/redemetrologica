<?php

namespace App\Http\Controllers;

use App\Models\Instrutor;
use Illuminate\Http\Request;

class InstrutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instrutores = Instrutor::all();
        return view('painel.instrutores.index', ['instrutores' => $instrutores]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function insert(Instrutor $instrutor)
    {
        return view('painel.instrutores.insert',  ['instrutor' => $instrutor]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
