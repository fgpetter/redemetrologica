<?php

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

Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');
Route::get('home', function () {
  return view('site.pages.site');
});
Route::get('noticias', function () {
  return view('site.pages.noticias');
});
Route::get('associe-se', function () {
  return view('site.pages.associe-se');
});
Route::get('cursos', function () {
  return view('site.pages.cursos');
});
Route::get('interlaboratoriais', function () {
  return view('site.pages.interlaboratoriais');
});
Route::get('laboratorios-avaliacao', function () {
  return view('site.pages.laboratorios-avaliacao');
});
Route::get('laboratorios-reconhecidos', function () {
  return view('site.pages.laboratorios-reconhecidos');
});
Route::get('bonus-metrologia', function () {
  return view('site.pages.bonus-metrologia');
});
Route::get('downloads', function () {
  return view('site.pages.downloads');
});
Route::get('fale-conosco', function () {
  return view('site.pages.fale-conosco');
});

Route::get('slug-da-noticia', function () {
  return view('site.pages.slug-da-noticia');
});

Route::get('slug-da-galeria', function () {
  return view('site.pages.slug-da-galeria');
});

Route::get('sobre', function () {
  return view('site.pages.sobre');
});


Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
