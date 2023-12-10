<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\AvaliadorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\DadoBancarioController;


Auth::routes();
//Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

/* Rotas estáticas */
Route::view('home', 'site.pages.site');
Route::view('noticias', 'site.pages.noticias');
Route::view('galerias', 'site.pages.galerias');
Route::view('associe-se', 'site.pages.associe-se');
Route::view('cursos', 'site.pages.cursos');
Route::view('interlaboratoriais', 'site.pages.interlaboratoriais');
Route::view('laboratorios-avaliacao', 'site.pages.laboratorios-avaliacao');
Route::view('laboratorios-reconhecidos', 'site.pages.laboratorios-reconhecidos');
Route::view('bonus-metrologia', 'site.pages.bonus-metrologia');
Route::view('downloads', 'site.pages.downloads');
Route::view('fale-conosco', 'site.pages.fale-conosco');
Route::view('slug-da-noticia', 'site.pages.slug-da-noticia');
Route::view('slug-da-galeria', 'site.pages.slug-da-galeria');
Route::view('sobre', 'site.pages.sobre');
Route::view('slug-interlaboratoriais', 'site.pages.slug-interlaboratoriais');
Route::view('slug-cursos', 'site.pages.slug-cursos');

/*Rotas das slugs (noticia e galeria) */
Route::get('noticias', [PostController::class, 'ListNoticias'])->name('show-list'); //mostra lista de noticias
Route::get('galerias', [PostController::class, 'ListGalerias'])->name('show-list'); //mostra lista de galerias
Route::get('noticia/{slug}', [PostController::class, 'show'])->name('noticia-show'); //mostra noticia
Route::get('galeria/{slug}', [PostController::class, 'show'])->name('galeria-show'); //mostra galeria

/* Rotas do template */
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

/* Rotas do painel */
Route::group(['prefix' => 'painel'], function () {

  /* Usuários */
  Route::group(['prefix' => 'user'], function () {
    Route::get('index', [UserController::class, 'index'])->name('user-index');
    Route::get('edit/{user}', [UserController::class, 'view'])->name('user-edit');
    Route::post('create', [UserController::class, 'create'])->name('user-create');
    Route::post('update/{user}', [UserController::class, 'update'])->name('user-update');
    Route::post('delete/{user}', [UserController::class, 'delete'])->name('user-delete');
  });

  /* Pessoas */
  Route::group(['prefix' => 'pessoa'], function () {
    Route::get('index', [PessoaController::class, 'index'])->name('pessoa-index');
    Route::get('insert/{pessoa:uid?}', [PessoaController::class, 'insert'])->name('pessoa-insert');
    Route::post('create', [PessoaController::class, 'create'])->name('pessoa-create');
    Route::post('update/{pessoa:uid}', [PessoaController::class, 'update'])->name('pessoa-update');
    Route::post('delete/{pessoa:uid}', [PessoaController::class, 'delete'])->name('pessoa-delete');
  });

  /* Endereços */
  Route::group(['prefix' => 'endereco'], function () {
    Route::post('create', [EnderecoController::class, 'create'])->name('endereco-create');
    Route::post('update/{endereco:uid}', [EnderecoController::class, 'update'])->name('endereco-update');
    Route::post('delete/{endereco:uid}', [EnderecoController::class, 'delete'])->name('endereco-delete');
  });

  /* Unidades */
  Route::group(['prefix' => 'unidade'], function () {
    Route::post('create', [UnidadeController::class, 'create'])->name('unidade-create');
    Route::post('update/{unidade:uid}', [UnidadeController::class, 'update'])->name('unidade-update');
    Route::post('delete/{unidade:uid}', [UnidadeController::class, 'delete'])->name('unidade-delete');
  });

  /* Funcionarios */
  Route::group(['prefix' => 'funcionario'], function () {
    Route::get('index', [FuncionarioController::class, 'index'])->name('funcionario-index');
    Route::get('insert/{funcionario:uid?}', [FuncionarioController::class, 'insert'])->name('funcionario-insert');
    Route::post('create', [FuncionarioController::class, 'create'])->name('funcionario-create');
    Route::post('update/{funcionario:uid}', [FuncionarioController::class, 'update'])->name('funcionario-update');
    Route::post('delete/{funcionario:uid}', [FuncionarioController::class, 'delete'])->name('funcionario-delete');
    Route::post('delete-curriculo/{funcionario:uid}', [FuncionarioController::class, 'curriculoDelete'])->name('curriculo-delete');
  });

  /* Avaliadores */
  Route::group(['prefix' => 'avaliador'], function () {
    Route::get('index', [AvaliadorController::class, 'index'])->name('avaliador-index');
    Route::get('insert/{avaliador:uid?}', [AvaliadorController::class, 'insert'])->name('avaliador-insert');
    Route::post('create', [AvaliadorController::class, 'create'])->name('avaliador-create');
    Route::post('update/{avaliador:uid}', [AvaliadorController::class, 'update'])->name('avaliador-update');
    Route::post('delete/{avaliador:uid}', [AvaliadorController::class, 'delete'])->name('avaliador-delete');
    Route::post('delete-curriculo/{avaliador:uid}', [FuncionarioController::class, 'curriculoDelete'])->name('avaliador-curriculo-delete');
  });

  /* Dados bancários */
  Route::group(['prefix' => 'conta'], function () {
    Route::post('create', [DadoBancarioController::class, 'create'])->name('conta-create');
    Route::post('update/{conta:uid}', [DadoBancarioController::class, 'update'])->name('conta-update');
    Route::post('delete/{conta:uid}', [DadoBancarioController::class, 'delete'])->name('conta-delete');
  });

  /* Cursos */
  Route::group(['prefix' => 'curso'], function () {
    Route::get('index', [CursoController::class, 'index'])->name('curso-index');
    Route::get('insert/{curso:uid?}', [CursoController::class, 'insert'])->name('curso-insert');
    Route::post('create', [CursoController::class, 'create'])->name('curso-create');
    Route::post('update/{curso:uid}', [CursoController::class, 'update'])->name('curso-update');
    Route::post('delete/{curso:uid}', [CursoController::class, 'delete'])->name('curso-delete');
  });

  /* Noticias e Galeria */
  Route::group(['prefix' => 'post'], function () {
    Route::get('noticias', [PostController::class, 'indexNoticias'])->name('noticia-index'); // tela de lista
    Route::get('galeria', [PostController::class, 'indexGaleria'])->name('galeria-index'); // tela de lista
    Route::get('noticia-insert/{post:slug?}', [PostController::class, 'noticiaInsert'])->name('noticia-insert'); // tela de edicao
    Route::get('galeria-insert/{post:slug?}', [PostController::class, 'galeriaInsert'])->name('galeria-insert'); // tela de edicao
    Route::post('create', [PostController::class, 'create'])->name('post-create'); // tela de cadastro
    Route::post('update/{post:slug}', [PostController::class, 'update'])->name('post-update'); // salvar
    Route::post('delete/{post:id}', [PostController::class, 'delete'])->name('post-delete');
    Route::post('image-upload', [PostController::class, 'storeImage'])->name('image-upload');
    Route::post('delete-thumb/{post:id}', [PostController::class, 'thumbDelete'])->name('thumb-delete'); //deletar thumb
  });
  
});
