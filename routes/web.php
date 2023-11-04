<?php

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
Route::get('sobre', function () {
  return view('site.pages.sobre');
});
Route::get('slug-interlaboratoriais', function () {
  return view('site.pages.slug-interlaboratoriais');
});
Route::get('slug-cursos', function () {
  return view('site.pages.slug-cursos');
});

/* Rotas do template */
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

/* Rotas do painel */
Route::group(['prefix' => 'painel'],function () {

  Route::group(['prefix' => 'user'],function () {
    Route::get('index', [UserController::class, 'index'])->name('user-index');
    Route::get('edit/{user}', [UserController::class, 'view'])->name('user-edit');
    Route::post('create', [UserController::class, 'create'] )->name('user-create');
    Route::post('update/{user}', [UserController::class, 'update'] )->name('user-update');
    Route::post('delete/{user}', [UserController::class, 'delete'] )->name('user-delete');
  });
});