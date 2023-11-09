<?php

use App\Http\Controllers\PessoaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();
//Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

/* Rotas estáticas */
Route::view('home', 'site.pages.site');
Route::view('noticias', 'site.pages.noticias');
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

/* Rotas do template */
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

/* Rotas do painel */
Route::group(['prefix' => 'painel'],function () {

  Route::group(['prefix' => 'user'],function () {
    Route::get('index', [UserController::class, 'index'])->name('user-index');
    Route::get('edit/{user}', [UserController::class, 'view'])->name('user-edit');
    Route::post('create', [UserController::class, 'create'] )->name('user-create');
    Route::post('update/{user}', [UserController::class, 'update'] )->name('user-update');
    Route::post('delete/{slug}', [UserController::class, 'delete'] )->name('user-delete');
  });

  Route::group(['prefix' => 'post'], function(){
    Route::get('noticias', [PostController::class, 'index'])->name('noticia-index'); // tela de lista
    Route::get('galeria', [PostController::class, 'index'])->name('galeria-index'); // tela de lista
    Route::post('create', [PostController::class, 'create'] )->name('post-create'); // tela de cadastro
    Route::get('edit/{post:slug}', [PostController::class, 'edit'])->name('post-edit'); // tela de edicao
    Route::post('update/{post:slug}', [PostController::class, 'update'] )->name('post-update'); // salvar
    Route::post('delete/{post:slug}', [PostController::class, 'delete'] )->name('post-delete');
  });

  /* Pessoas */
  Route::group(['prefix' => 'pessoa'], function(){
    Route::get('index', [PessoaController::class, 'index'])->name('pessoa-index');
    Route::get('insert/{pessoa?}', [PessoaController::class, 'insert'])->name('pessoa-insert');
    Route::post('create', [PessoaController::class, 'create'] )->name('pessoa-create');
    Route::get('edit/{pessoa}', [PessoaController::class, 'edit'])->name('pessoa-edit');
    Route::post('update/{pessoa}', [PessoaController::class, 'update'] )->name('pessoa-update');
    Route::post('delete/{pessoa}', [PessoaController::class, 'delete'] )->name('pessoa-delete');

  });

});