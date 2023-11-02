<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

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

/* Rotas do template */
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

/* Rotas do painel */
Route::group(['prefix' => 'painel'],function () {
  Route::view('user-list', 'users.user-list')->name('user-list');
  Route::view('user/{id}', 'users.user-edit')->name('user');
  Route::post('user-create', [UserController::class, 'create'] )->name('user-create');
});