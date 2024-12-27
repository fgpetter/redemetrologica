<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    public function root()
    {
        $DataAtual = \Carbon\Carbon::now();
        $galerias = Post::where('tipo', 'galeria')
            ->where('data_publicacao', '<=', $DataAtual)
            ->where('rascunho', 0)
            ->orderBy('data_publicacao', 'desc') //ordenar por data_publicacao
            ->take(4) // Pegar apenas os últimos 4 registros
            ->get();
        foreach ($galerias as $galeria) {
            // Trata a data
            $galeria->data_publicacao = \Carbon\Carbon::parse($galeria->data_publicacao)->format('d/m/Y');
        }
        $noticias = Post::where('tipo', 'noticia')
            ->where('data_publicacao', '<=', $DataAtual)
            ->where('rascunho', 0)
            ->orderBy('data_publicacao', 'desc') //ordenar por data_publicacao
            ->take(4) // Pegar apenas os últimos 4 registros
            ->get();
        foreach ($noticias as $noticia) {
            // Remove todas as tags de imagem
            $conteudoSemImagens = preg_replace('/<img[^>]+\>/i', '', $noticia->conteudo);
            //Remove tags e "nbsp"
            $textoSemHTML = strip_tags($conteudoSemImagens);
            $textoSemHTML = str_replace('nbsp', '', $textoSemHTML);
            // Limita o conteúdo para as primeiras 25 palavras
            $primeirasDezPalavras = implode(' ', array_slice(str_word_count($textoSemHTML, 2), 0, 25));
            // Atualiza o conteúdo do noticia
            $noticia->conteudo = $primeirasDezPalavras . "...";
            // Trata a data
            $noticia->data_publicacao = \Carbon\Carbon::parse($noticia->data_publicacao)->format('d/m/Y');
        }
        return view('site.pages.site', ['galerias' => $galerias, 'noticias' => $noticias]);
    }


    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }
}
