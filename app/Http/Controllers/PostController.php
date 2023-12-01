<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
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

        return view('post.noticia-index', ['posts' => $posts]);
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
        return view('post.noticia-index', ['posts' => $posts]);
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
            if ($img->height() > 750) {
                $img->resize(null, 750, function ($constraint) {
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
        //se for criado como galeria, coloca a palavra galeria como padrão no campo conteudo.
        $request->merge(['conteudo' => $request->get('tipo') === 'galeria' ? 'galeria' : $request->get('conteudo')]);
        $request->validate(
            [
                'titulo' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($request->post)], //valida sl
                'conteudo' => ['required', 'string'],
                'thumb' => ['required', 'image', 'mimes:jpg,png,jpeg'],
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
                'thumb.required' => 'Imagem de capa é obrigatória'
            ]
        );
        if ($request->hasFile('thumb')) { //thumb
            $originName = $request->file('thumb')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $fileName = str_replace(' ', '-', $fileName);
            $extension = $request->file('thumb')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
            $request->file('thumb')->move(public_path('post-media'), $fileName);

            // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
            $img = Image::make(public_path('post-media/' . $fileName));
            if ($img->height() > 750) {
                $img->resize(null, 750, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $img->encode('jpg', 75);
            $img->save(public_path('post-media/' . $fileName));

            $image = 'post-media/' . $fileName;
        }
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
        // //se for criado como galeria, coloca a palavra galeria como padrão no campo conteudo.
        // $request->merge(['conteudo' => $request->get('tipo') === 'galeria' ? 'galeria' : $request->get('conteudo')]);
        $request->validate(
            [
                'titulo' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($request->post)], //valida sl
                'conteudo' => ['required', 'string'],
                // 'thumb' => ['required', 'image', 'mimes:jpg,png,jpeg'],
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
        //thumb
        if ($request->hasFile('thumb')) {
            $originName = $request->file('thumb')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $fileName = str_replace(' ', '-', $fileName);
            $extension = $request->file('thumb')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
            $request->file('thumb')->move(public_path('post-media'), $fileName);

            // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
            $img = Image::make(public_path('post-media/' . $fileName));
            if ($img->height() > 750) {
                $img->resize(null, 750, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $img->encode('jpg', 75);
            $img->save(public_path('post-media/' . $fileName));

            $image = 'post-media/' . $fileName;

            // deleta arquivo anterior
            if (File::exists(public_path($post->thumb))) {
                File::delete(public_path($post->thumb));
            }
        } else {
            $image = $post->thumb;
        }
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
    public function delete(Request $request, Post $post, PostMedia $postMedia): RedirectResponse
    {
        //deleta a thumb
        if (File::exists(public_path($post->thumb))) {
            File::delete(public_path($post->thumb));
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

        return redirect()->route('noticia-index')->with('update-success', 'Post removido');;
    }
}
