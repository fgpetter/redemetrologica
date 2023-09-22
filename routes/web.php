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
  return view('site.site');
});
Route::get('noticias', function () {
  return view('site.noticias');
});
Route::get('associe-se', function () {
  return view('site.associe-se');
});
Route::get('cursos', function () {
  return view('site.cursos');
});
Route::get('interlaboratoriais', function () {
  return view('site.interlaboratoriais');
});
Route::get('laboratorios-avaliacao', function () {
  return view('site.laboratorios-avaliacao');
});
Route::get('laboratorios-reconhecidos', function () {
  return view('site.laboratorios-reconhecidos');
});
Route::get('bonus-metrologia', function () {
  return view('site.bonus-metrologia');
});
Route::get('downloads', function () {
  return view('site.downloads');
});
Route::get('fale-conosco', function () {
  return view('site.fale-conosco');
});



Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
