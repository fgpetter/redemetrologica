<?php

use App\Http\Controllers\PessoaController;

Route::get('index', [PessoaController::class, 'index'])->name('pessoa-index');
Route::get('insert/{pessoa:uid?}', [PessoaController::class, 'insert'])->name('pessoa-insert');
Route::post('create', [PessoaController::class, 'create'])->name('pessoa-create');
Route::post('update/{pessoa:uid}', [PessoaController::class, 'update'])->name('pessoa-update');
Route::post('delete/{pessoa:uid}', [PessoaController::class, 'delete'])->name('pessoa-delete');
Route::post('associa-empresa/{pessoa:uid}', [PessoaController::class, 'associaEmpresa'])->name('pessoa-associa-empresa');
Route::post('associa-usuario/{pessoa:uid}', [PessoaController::class, 'associaUsuario'])->name('pessoa-associa-usuario');