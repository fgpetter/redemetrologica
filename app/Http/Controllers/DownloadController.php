<?php

namespace App\Http\Controllers;

use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

class DownloadController extends Controller
{
    /**
     * Lista de downloads
     * @return View
     */
    public function index(): View
    {
        $downloads = Download::all();
        return view('painel.downloads.index', ['downloads' => $downloads]);
    }

    /**
     * Tela de edição de download
     *
     * @param Download $download
     * @return View
     */
    public function insert(Download $download): View
    {
        return view('painel.downloads.insert', ['download' => $download]);
    }

    /**
     * Cria um novo download
     *
     * @param Request $request description
     * @return RedirectResponse
     */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['nullable', 'string', 'max:191'],
            'descricao' => ['nullable', 'string', 'max:191'],
            'categoria' => ['nullable', 'in:CURSOS,QUALIDADE,INTERLAB,INSTITUCIONAL'],
            'arquivo' => ['required', 'file', 'max:2048', 'mimes:doc,pdf,docx'],
        ],[
            'arquivo.max' => 'O arquivo ultrapassa o limite de 2MB',
            'arquivo.mimes' => 'O arquivo deve ser do tipo: doc, pdf, docx',
            'arquivo.required' => 'O arquivo deve ser selecionado',
            'titulo.max' => 'O valor informado ultrapassa o limite de :max caracteres',
            'titulo.string' => 'O valor informado tem caracteres inválidos',
            'descricao.max' => 'O valor informado ultrapassa o limite de :max caracteres',
            'descricao.string' => 'O valor informado tem caracteres inválidos',
            'categoria.max' => 'O valor informado ultrapassa o limite de :max caracteres',
            'categoria.string' => 'O valor informado tem caracteres inválidos',
        ]);

        if ($request->hasFile('arquivo')) { //arquivo
            $originName = $request->file('arquivo')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $fileName = str_replace(' ', '-', $fileName);
            $extension = $request->file('arquivo')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
            $request->file('arquivo')->move(public_path('downloads'), $fileName);            
        }

        $validated['arquivo'] = $fileName;

        Download::create($validated);
        return redirect()->route('download-index')->with('success', 'Download criado com sucesso');

    }

    /**
     * Edita um download
     *
     * @param Request $request description
     * @param Doed $download
     * @return RedirectResponse
     */
    public function update(Request $request, Download $download): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['nullable', 'string', 'max:191'],
            'descricao' => ['nullable', 'string', 'max:191'],
            'categoria' => ['nullable', 'string', 'max:191'],
            'arquivo' => ['nullable', 'file', 'max:2048', 'mimes:doc,pdf,docx'],
        ],[
            'arquivo.max' => 'O arquivo ultrapassa o limite de 2MB',
            'arquivo.mimes' => 'O arquivo deve ser do tipo: doc, pdf, docx',
            'titulo.max' => 'O valor informado ultrapassa o limite de :max caracteres',
            'titulo.string' => 'O valor informado tem caracteres inválidos',
            'descricao.max' => 'O valor informado ultrapassa o limite de :max caracteres',
            'descricao.string' => 'O valor informado tem caracteres inválidos',
            'categoria.max' => 'O valor informado ultrapassa o limite de :max caracteres',
            'categoria.string' => 'O valor informado tem caracteres inválidos',
        ]);

        if ($request->hasFile('arquivo')) {

            File::delete(public_path('downloads/'.$download->arquivo));

            $originName = $request->file('arquivo')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $fileName = str_replace(' ', '-', $fileName);
            $extension = $request->file('arquivo')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
            $request->file('arquivo')->move(public_path('downloads'), $fileName);
            
            $validated['arquivo'] = $fileName;
        } else {
            unset($validated['arquivo']);
        }

        $download->update($validated);
        return redirect()->route('download-index')->with('success', 'Download editado com sucesso');
    }

    public function delete(Download $download): RedirectResponse
    {
        if (File::exists(public_path('downloads/'.$download->arquivo))) {
            File::delete(public_path('downloads/'.$download->arquivo));
        }

        $download->delete();
        return redirect()->route('download-index')->with('warning', 'Download removido com sucesso');
    }

    
    public function siteIndex(Request $request): View
    {
        $categoria = preg_replace('/[^A-Za-z0-9\-]/', '', $request->input('categoria'));
        $titulo = preg_replace('/[^A-Za-z0-9\-]/', '', $request->input('descricao'));

        $downloads = Download::select('titulo', 'descricao', 'arquivo','categoria')
            ->when($categoria, function ($query) use ($categoria) {
                return $query->where('categoria', $categoria);
            })
            ->when($titulo, function ($query) use ($titulo) {
                return $query->where('titulo', 'like', "%$titulo%")->orWhere('descricao', 'like', "%$titulo%");
            })->get();

        return view('site.pages.downloads', ['downloads' => $downloads]);
    }

}
