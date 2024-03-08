<?php

namespace App\Http\Controllers;

use App\Models\PostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PostMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(string $id)
    {
        //
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
    public function destroy($id)
    {
        $postMedia = PostMedia::find($id);

        // Deleta o arquivo da imagem
        File::delete(public_path($postMedia->caminho_media));

        // Deleta o registro da tabela post_media
        $postMedia->delete();

        return redirect()->back()->with('warning', 'Imagem excluída com sucesso!');
    }
}
