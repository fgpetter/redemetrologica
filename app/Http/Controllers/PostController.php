<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Validation\Rule;

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
    $request->validate(
      [
        'titulo' => ['required', 'string', 'max:255'],
        'conteudo' => ['required', 'string'],
        //'thumb' => ['required', 'image', 'mimes:jpg,png,jpeg'],
        'data_publicacao' => ['required', 'date'],
      ],
      [
        'titulo.required' => 'Preencha o campo titulo',
        'titulo.string' => 'O campo titulo tem caracteres inválidos',
        'titulo.max' => 'O campo titulo aceita até 250 caracteres',
        'conteudo.required' => 'Preencha o campo conteudo',
        'conteudo.string' => 'O campo conteudo tem caracteres inválidos',
        'data_publicacao.required' => 'Preencha o campo Data de publicação',
        'data_publicacao.date' => 'Data de publicação invalida',
      ]
    );

    // Upload de imagem
    // $imageName = time().'.'.$request->image->extension();
    // $request->image->move(public_path('images'), $imageName);
    //'thumb' => '/site/imagens/'.$imageName,
    // vamos usar esse https://image.intervention.io/v2/api/resize para tratar imagem

    $posts = Post::create([
      'titulo' => $request->get('titulo'),
      'slug' => Str::slug($request->get('titulo'), '-'),
      'conteudo' => $request->get('conteudo'),
      'thumb' => $request->get('thumb'),
      'rascunho' => $request->get('rascunho') ?? 0,
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
  public function update(Request $request, Post $post): RedirectResponse
  {

    $request->validate(
      [
        'titulo' => ['required', 'string', 'max:255'],
        'conteudo' => ['required', 'string'],
        //'thumb' => ['required', 'image', 'mimes:jpg,png,jpeg'],
        'data_publicacao' => ['required', 'date'],
      ],
      [
        'titulo.required' => 'Preencha o campo titulo',
        'titulo.string' => 'O campo titulo tem caracteres inválidos',
        'titulo.max' => 'O campo titulo aceita até 250 caracteres',
        'conteudo.required' => 'Preencha o campo conteudo',
        'conteudo.string' => 'O campo conteudo tem caracteres inválidos',
        'data_publicacao.required' => 'Preencha o campo Data de publicação',
        'data_publicacao.date' => 'Data de publicação invalida',

      ]
    );
    $post->update([
      'titulo' => $request->get('titulo'),
      'slug' => Str::slug($request->get('titulo'), '-'),
      'conteudo' => $request->get('conteudo'),
      'thumb' => $request->get('thumb'),
      'rascunho' => $request->get('rascunho') ?? 0,
      'tipo' => $request->get('tipo'),
      'data_publicacao' => $request->get('data_publicacao')
    ]);

    if (!$post) {
      return redirect()->back()->withInput($request->input())->with('error', 'Ocorreu um erro!');
    }

    if ($post->tipo == 'noticia') {
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

  /**
   * Salva imagem do form de conteúdo
   *
   * @param Request $request
   * @return Response
   */
  public function storeImage(Request $request)
  {
    if ($request->hasFile('upload')) {
      $originName = $request->file('upload')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $extension = $request->file('upload')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;

      $request->file('upload')->move(public_path('post-media'), $fileName);

      $url = asset('post-media/' . $fileName);
      return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
    }
  }
}
