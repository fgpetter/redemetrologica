<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class PostController extends Controller
{
    /**
     * Gera pagina de listagem de posts de noticias
     *
     * @return View
     **/
    public function indexNoticias(): View
    {
        $posts = Post::where('tipo', 'noticia')->get();
        return view('post.noticia-index', ['posts' => $posts]);
    }
    /**
     * Gera pagina de listagem de posts de galeria
     *
     * @return View
     **/
    public function indexGaleria(): View
    {
        $posts = Post::where('tipo', 'galeria')->get();
        return view('post.noticia-index', ['posts' => $posts]);
    }


    /**
     * Adiciona posts na base
     *
     * @param Request $request
     * @return RedirectResponse
     **/
    public function create(Request $request): RedirectResponse
    {
        // $request->validate(
        //     [
        //         'nome_razao' => ['required', 'string', 'max:255'],
        //         'cpf_cnpj' => ['required', 'string', 'max:255'], // TODO - adicionar validação de CPF/CNPJ
        //         'tipo_pessoa' => ['required', 'string', 'max:2'],
        //     ],
        //     [
        //         'nome_razao.required' => 'Preencha o campo nome ou razão social',
        //         'cpf_cnpj.required' => 'Preencha o campo documento',
        //     ]
        // );

        $posts = Post::create([

            'id' => $request->get('id'),
            'titulo' => $request->get('titulo'),
            'slug' => $request->get('slug'),
            'conteudo' => $request->get('conteudo'),
            'thumb' => $request->get('thumb'),
            'rascunho' => $request->get('rascunho'),
            'tipo' => $request->get('tipo'),
            'data_publicacao' => $request->get('data_publicacao')

        ]);

        if (!$posts) {
            return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
        }



        if ($posts->tipo == 'noticia') {
            return redirect()->route('noticia-index')->with('update-success', 'Noticia adicionada');
        }
        return redirect()->route('galeria-index')->with('update-success', 'Galeria adicionada');
    }
    /**
     * Tela de edição de post
     *
     * @param Post $post
     * @return View
     **/
    public function insert(Post $post): View
    {
        return view('post.insert', ['post' => $post]);
    }

    /**
     * Edita dados de post
     *
     * @param Request $request
     * @param Post $posts
     * @return RedirectResponse
     **/
    public function update(Request $request, Post $posts): RedirectResponse
    {
        // $request->validate(
        //     [


        //         'slug' => ['unique:posts,slug,' . $posts->slug, 'required', 'string', 'slug'],
        //     ],
        //     [

        //         'slug.required' => 'Preencha o campo slug'
        //     ]
        // );
        $posts->update([
            'id' => $request->get('id'),
            'titulo' => $request->get('titulo'),
            'slug' => $request->get('slug'),
            'conteudo' => $request->get('conteudo'),
            'thumb' => $request->get('thumb'),
            'rascunho' => $request->get('rascunho'),
            'tipo' => $request->get('tipo'),
            'data_publicacao' => $request->get('data_publicacao')
        ]);


        if (!$posts) {
            return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
        }
        // dd($posts);

        if ($posts->tipo == 'noticia') {
            return redirect()->route('noticia-index')->with('update-success', 'Noticia atualizada');
        }
        return redirect()->route('galeria-index')->with('update-success', 'Galeria atualizada');
    }



    /**
     * Remove post
     *
     * @param Request $request
     * @param User $post
     * @return RedirectResponse
     **/
    public function delete(Request $request, Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('noticia-index')->with('update-success', 'Post removido');;
    }
}
