<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
  public $PastaTemp;
  /**
   * Gera pagina de listagem de posts de noticias
   *
   * @return View
   **/
  public function indexNoticias(): View
  {
    $posts = Post::where('tipo', 'noticia')
      ->orderBy('data_publicacao', 'desc') //ordenar por data_publicacao
      ->get();

    return view('painel.post.noticia-index', ['posts' => $posts, 'tipo' => 'noticia']);
  }

  /**
   * Gera pagina de listagem de posts de galeria
   *
   * @return View
   **/
  public function indexGaleria(): View
  {
    $posts = Post::where('tipo', 'galeria')
      ->orderBy('data_publicacao', 'desc') //ordenar por data_publicacao
      ->get();
    return view('painel.post.noticia-index', ['posts' => $posts, 'tipo' => 'galeria']);
  }

  /**
   * Salva imagem do form de conteúdo
   *
   * @param Request $request
   * @return Response
   */
  public function storeImage(Request $request)
  {
    if ($request->hasFile('upload')) { //imagem de post
      $originName = $request->file('upload')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '_', $fileName);
      $extension = $request->file('upload')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $this->PastaTemp = 'Temp' . substr(hrtime(true), -9, 9);
      // obtenha a pasta temporária atual da sessão
      $tempPastas = $request->session()->get('tempPastas', []);
      // adicione a nova pasta temporária no array
      $tempPastas[] = $this->PastaTemp;
      // atualize a sessão com o novo array
      $request->session()->put('tempPastas', $tempPastas);
      //move o arquivo
      $request->file('upload')->move(public_path($this->PastaTemp), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      $img = Image::make(public_path($this->PastaTemp . '/' . $fileName));
      // limita a imagem em 750px de altura
      if ($img->height() > 750) {
        $img->resize(null, 750, function ($constraint) {
          $constraint->aspectRatio();
        });
      }
      // limita a imagem em 750px de largura
      if ($img->width() > 750) {
        $img->resize(750, null, function ($constraint) {
          $constraint->aspectRatio();
        });
      }
      $img->encode('jpg', 75);
      $img->save(public_path($this->PastaTemp . '/' . $fileName));

      $url = asset($this->PastaTemp  . '/' . $fileName);
      return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
    }
  }

  /**
   * Adiciona posts na base
   *
   * @param Request $request
   * @return RedirectResponse
   **/
  public function create(Request $request): RedirectResponse
  {
    if (!$request->has('thumb')) {
      $request->merge(['thumb' => null]);
    }

    $request->validate(
      [
        'titulo' => ['required', 'string', 'max:191'],
        'conteudo' => ['required_if:tipo,noticia', 'string'],
        'thumb' => ['required', 'image', 'mimes:jpg,png,jpeg'],
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
        'thumb.required' => 'Imagem de capa é obrigatória'
      ]
    );



    if (Post::where('slug', Str::slug($request->get('titulo'), '-'))->exists()) {
      return redirect()->back()->withInput()->with('error', 'Outro post com esse título já existe');
    }

    if ($request->hasFile('thumb')) { //thumb
      $originName = $request->file('thumb')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('thumb')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('thumb')->move(public_path('post-media'), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      $img = Image::make(public_path('post-media/' . $fileName));
      if ($img->height() > 1250) {
        $img->resize(null, 750, function ($constraint) {
          $constraint->aspectRatio();
        });
      }
      $img->encode('jpg', 75);
      $img->save(public_path('post-media/' . $fileName));

      $image = $fileName;
    }
    // fotos galeria
    if ($request->hasFile('imagens')) {
      foreach ($request->file('imagens') as $file) {
        $originName = $file->getClientOriginalName();
        $fileName = pathinfo($originName, PATHINFO_FILENAME);
        $fileName = str_replace(' ', '-', $fileName);
        $extension = $file->getClientOriginalExtension();
        $fileName = $fileName . '_' . time() . '.' . $extension;
        $file->move(public_path('post-media'), $fileName);

        // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
        $img = Image::make(public_path('post-media/' . $fileName));
        if ($img->height() > 750) {
          $img->resize(null, 750, function ($constraint) {
            $constraint->aspectRatio();
          });
        }
        $img->encode('jpg', 75);
        $img->save(public_path('post-media/' . $fileName));

        $imagePath =  $fileName;

        // Adicione o caminho da imagem na tabela de postmedia
        $postMedia = PostMedia::create([
          'slug_post' => Str::slug($request->get('titulo'), '-'),
          'caminho_media' => $imagePath,
        ]);
      }
    }
    // fotos galeria

    if (session()->has('tempPastas')) {
      $tempPastas = session()->get('tempPastas');
      $conteudo = $request->get('conteudo');
      //troca a pasta temporaria pela permanente no $conteudo
      foreach ($tempPastas as $tempPasta) {
        $conteudo = str_replace($tempPasta, 'post-media', $conteudo);
      }

      foreach ($tempPastas as $tempPasta) {
        $tempMediaPath = public_path($tempPasta);
        $postMediaPath = public_path('post-media');

        $files = File::allFiles($tempMediaPath);

        foreach ($files as $file) {
          $destinationPath = $postMediaPath . DIRECTORY_SEPARATOR . $file->getFilename();

          // copia se o arquivo não existir
          if (!File::exists($destinationPath)) {
            File::copy($file->getPathname(), $destinationPath);

            // Adiciona a caminho_media na tabela de postmedia
            $postMedia = PostMedia::create([
              'slug_post' => Str::slug($request->get('titulo'), '-'),
              'caminho_media' => $file->getFilename(),
            ]);

            $postMedia->save();
          }
        }
        // deleta pasta temporaria
        File::deleteDirectory($tempMediaPath);
      }

      // limpa a session
      session()->forget('tempPastas');
    }
    $posts = Post::create([
      'titulo' => $request->get('titulo'),
      'slug' => Str::slug($request->get('titulo'), '-'),
      'conteudo' => $conteudo ?? $request->get('conteudo'),
      'thumb' => $image ?? $request->get('thumb'),
      'rascunho' => $request->get('rascunho') ?? 0,
      'tipo' => $request->get('tipo'),
      'data_publicacao' => $request->get('data_publicacao')
    ]);
    if (!$posts) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }

    if ($posts->tipo == 'noticia') {
      return redirect()->route('noticia-index')->with('success', 'Noticia adicionada');
    }
    return redirect()->route('galeria-index')->with('success', 'Galeria adicionada');
  }

  /**
   * Tela de edição de noticia
   *
   * @param Post $post
   * @return View
   **/
  public function noticiaInsert(Post $post): View
  {
    $postMedia = PostMedia::where('slug_post', $post->slug)->get();
    return view('painel.post.insert', ['post' => $post, 'postMedia' => $postMedia,  'tipo' => 'noticia']);
  }

  /**
   * Tela de edição de galeria
   *
   * @param Post $post
   * @return View
   **/
  public function galeriaInsert(Post $post): View
  {
    $postMedia = PostMedia::where('slug_post', $post->slug)->get();
    return view('painel.post.insert', ['post' => $post, 'postMedia' => $postMedia, 'tipo' => 'galeria']);
  }


  /**
   * Edita dados de post
   *
   * @param Request $request
   * @param Post $post
   * @return RedirectResponse
   **/
  public function update(Request $request, Post $post): RedirectResponse
  {
    if (!$request->has('thumb')) {
      $request->merge(['thumb' => null]);
    }


    $request->validate(
      [
        'titulo' => ['required', 'string', 'max:191'],
        'conteudo' => ['required_if:tipo,noticia', 'string'],
        'thumb' => ['required',  'sometimes:string', 'sometimes:image', 'sometimes:mimes:jpg,png,jpeg'],
        'data_publicacao' => ['required', 'date'],
      ],
      [
        'titulo.required' => 'Preencha o campo titulo',
        'titulo.string' => 'O campo titulo tem caracteres inválidos',
        'titulo.max' => 'O campo titulo aceita até 250 caracteres',
        'titulo.unique' => 'O título já está em uso.',
        'conteudo.required' => 'Preencha o campo conteudo',
        'conteudo.string' => 'O campo conteudo tem caracteres inválidos',
        'data_publicacao.required' => 'Preencha o campo Data de publicação',
        'data_publicacao.date' => 'Data de publicação invalida',
      ]
    );

    //verifica no update se o titulo foi alterado para um que já exista na base.
    if ($post->slug != Str::slug($request->get('titulo'), '-')) {
      if (Post::where('slug', Str::slug($request->get('titulo'), '-'))->exists()) {
        return redirect()->back()->withInput()->with('error', 'Outro post com esse título já existe');
      }
    }



    if ($request->hasFile('thumb')) {
      $originName = $request->file('thumb')->getClientOriginalName();
      $fileName = pathinfo($originName, PATHINFO_FILENAME);
      $fileName = str_replace(' ', '-', $fileName);
      $extension = $request->file('thumb')->getClientOriginalExtension();
      $fileName = $fileName . '_' . time() . '.' . $extension;
      $request->file('thumb')->move(public_path('post-media'), $fileName);

      // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
      $img = Image::make(public_path('post-media/' . $fileName));
      if ($img->height() > 1250) {
        $img->resize(null, 750, function ($constraint) {
          $constraint->aspectRatio();
        });
      }
      $img->encode('jpg', 75);
      $img->save(public_path('post-media/' . $fileName));

      $image = $fileName;

      // deleta arquivo anterior
      if (File::exists(public_path('post-media/' . $post->thumb))) {
        File::delete(public_path('post-media/' . $post->thumb));
      }
    } else {
      $image = $post->thumb;
    }

    // fotos galeria
    if ($request->hasFile('imagens')) {
      foreach ($request->file('imagens') as $file) {
        $originName = $file->getClientOriginalName();
        $fileName = pathinfo($originName, PATHINFO_FILENAME);
        $fileName = str_replace(' ', '-', $fileName);
        $extension = $file->getClientOriginalExtension();
        $fileName = $fileName . '_' . time() . '.' . $extension;
        $file->move(public_path('post-media'), $fileName);

        // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
        $img = Image::make(public_path('post-media/' . $fileName));
        if ($img->height() > 750) {
          $img->resize(null, 750, function ($constraint) {
            $constraint->aspectRatio();
          });
        }
        $img->encode('jpg', 75);
        $img->save(public_path('post-media/' . $fileName));

        $imagePath =  $fileName;


        // Adicione o caminho da imagem na tabela de postmedia
        $postMedia = PostMedia::create([
          'slug_post' => Str::slug($request->get('titulo'), '-'),
          'caminho_media' => $imagePath,

        ]);
      }
    }
    // fotos galeria

    if (session()->has('tempPastas')) {
      $tempPastas = session()->get('tempPastas');
      $conteudo = $request->get('conteudo');
      //troca a pasta temporaria pela permanente no $conteudo
      foreach ($tempPastas as $tempPasta) {
        $conteudo = str_replace($tempPasta, 'post-media', $conteudo);
      }
      foreach ($tempPastas as $tempPasta) {
        $tempMediaPath = public_path($tempPasta);
        $postMediaPath = public_path('post-media');
        $files = File::allFiles($tempMediaPath);
        foreach ($files as $file) {
          $destinationPath = $postMediaPath . DIRECTORY_SEPARATOR . $file->getFilename();
          // copia se o arquivo não existir
          if (!File::exists($destinationPath)) {
            File::copy($file->getPathname(), $destinationPath);

            //insere os nomes dos arquivos da $tempMediaPath na $postMedia
            $postMedia = PostMedia::create([
              'slug_post' => Str::slug($request->get('titulo'), '-'),
              'caminho_media' => $file->getFilename(),
            ]);
            $postMedia->save();
          }
        }
        // deleta pasta temporaria
        File::deleteDirectory($tempMediaPath);
      }
      // limpa a session
      session()->forget('tempPastas');
    }

    $post->update([
      'titulo' => $request->get('titulo'),
      'slug' => Str::slug($request->get('titulo'), '-'),
      'conteudo' => $conteudo ?? $request->get('conteudo'),
      'thumb' => $image ?? $request->get('thumb'),
      'rascunho' => $request->get('rascunho') ?? 0,
      'tipo' => $request->get('tipo'),
      'data_publicacao' => $request->get('data_publicacao')
    ]);

    //     para cada caminho_media na $postMedia com mesmo slug_post
    //     varrer $conteudo e deletar o arquivo da  $postMediaPath  que não encontrar correspondencia dentro da string
    //     e em seguida deletar o registro do arquivo na $postMedia

    // todos os caminho_media relacionados a este post
    if ($post->tipo == 'noticia') {
      $postMediaItems = PostMedia::where('slug_post', $post->slug)->get();
      foreach ($postMediaItems as $postMediaItem) {
        // procura se tem dentro do conteudo
        if (strpos($post->conteudo, $postMediaItem->caminho_media) === false) {
          // deleta o arquivo do diretório
          $fileToDelete = public_path('post-media/' . $postMediaItem->caminho_media);
          if (File::exists($fileToDelete)) {
            File::delete($fileToDelete);
          }
          // deleta o registro da tabela post_media
          $postMediaItem->delete();
        }
      }
    }
    //deleta fotos que foram descartados no update da galeria e adicionados a listanegra
    if ($request->has('deleteList')) {
      $deleteList = explode(',', $request->input('deleteList'));
      foreach ($deleteList as $id) {
        // Recupera o registro PostMedia pelo id
        $postMediaItem = PostMedia::find($id);

        if ($postMediaItem) {
          // Deleta o arquivo do diretório
          $fileToDelete = public_path('post-media/' . $postMediaItem->caminho_media);
          if (File::exists($fileToDelete)) {
            File::delete($fileToDelete);
          }

          // Deleta o registro da tabela post_media
          $postMediaItem->delete();
        }
      }
    }

    if (!$post) {
      return redirect()->back()->with('error', 'Ocorreu um erro!');
    }
    if ($post->tipo == 'noticia') {
      return redirect()->route('noticia-index')->with('success', 'Noticia atualizada');
    }
    return redirect()->route('galeria-index')->with('success', 'Galeria atualizada');
  }

  /**
   * Remove arquivo de thumb
   *
   * @param User $user
   * @return RedirectResponse
   **/
  public function thumbDelete(Post $post): RedirectResponse
  {
    // deleta arquivo anterior
    if (File::exists(public_path($post->thumb))) {
      File::delete(public_path($post->thumb));
    }

    $post->update(['thumb' => null]);

    return redirect()->back()->with('success', 'thumb removido');
  }

  /**
   * Remove post
   *
   * @param Post $post
   * @return RedirectResponse
   **/
  public function delete(Post $post): RedirectResponse
  {
    //deleta a thumb
    if (File::exists(public_path('post-media/' . $post->thumb))) {
      File::delete(public_path('post-media/' . $post->thumb));
    }
    // todos os caminho_media relacionados a este post
    $postMediaItems = PostMedia::where('slug_post', $post->slug)->get();
    foreach ($postMediaItems as $postMediaItem) {

      // deleta o arquivo do diretório
      $fileToDelete = public_path('post-media/' . $postMediaItem->caminho_media);
      if (File::exists($fileToDelete)) {
        File::delete($fileToDelete);
      }
      // deleta o registro da tabela post_media
      $postMediaItem->delete();
    }
    $post->delete();



    if ($post->tipo == 'noticia') {
      return redirect()->route('noticia-index')->with('warning', 'Noticia removida');
    }
    return redirect()->route('galeria-index')->with('warning', 'Galeria removida');
  }

  /**
   * Gera pagina de listagem de posts de noticias no site
   *
   * @return View
   **/
  public function ListNoticias(): View
  {
    $DataAtual = \Carbon\Carbon::now();
    $posts = Post::where('tipo', 'noticia')
      ->where('data_publicacao', '<=', $DataAtual)
      ->where('rascunho', 0)
      ->orderBy('data_publicacao', 'desc') //ordenar por data_publicacao
      ->get();

    foreach ($posts as $post) {
      // Remove todas as tags de imagem
      $conteudoSemImagens = preg_replace('/<img[^>]+\>/i', '', $post->conteudo);
      //Remove tags e "nbsp"
      $textoSemHTML = strip_tags($conteudoSemImagens);
      $textoSemHTML = str_replace('nbsp', '', $textoSemHTML);
      // Limita o conteúdo para as primeiras 25 palavras
      $primeirasDezPalavras = implode(' ', array_slice(str_word_count($textoSemHTML, 2), 0, 25));
      // Atualiza o conteúdo do post
      $post->conteudo = $primeirasDezPalavras . "...";
      // Trata a data
      $post->data_publicacao = \Carbon\Carbon::parse($post->data_publicacao)->format('d/m/Y');
    }

    return view('site.pages.noticias', ['posts' => $posts, 'tipo' => 'noticia']);
  }

  /**
   * Gera pagina de listagem de posts de galeria no site
   *
   * @return View
   **/
  public function ListGalerias(): View
  {
    $DataAtual = \Carbon\Carbon::now();
    $posts = Post::where('tipo', 'galeria')
      ->where('data_publicacao', '<=', $DataAtual)
      ->where('rascunho', 0)
      ->orderBy('data_publicacao', 'desc') //ordenar por data_publicacao
      ->get();
    foreach ($posts as $post) {
      // Trata a data
      $post->data_publicacao = \Carbon\Carbon::parse($post->data_publicacao)->format('d/m/Y');
    }
    return view('site.pages.galerias', ['posts' => $posts, 'tipo' => 'galeria']);
  }

  /**
   * mostra pagina da slug da noticia/galeria no site
   *
   * @param string $slug
   * @return View
   **/
  public function show($slug): View
  {
    $postMedia = PostMedia::where('slug_post', $slug)->get();
    $post = Post::where('slug', $slug)->firstOrFail();
    return view('site.pages.slug-da-noticia', ['post' => $post, 'postMedia' => $postMedia]);
  }
}
