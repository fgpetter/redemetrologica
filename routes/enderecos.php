<?php

use App\Http\Controllers\EnderecoController;

Route::post('create', [EnderecoController::class, 'create'])->name('endereco-create');
Route::post('update/{endereco:uid}', [EnderecoController::class, 'update'])->name('endereco-update');
Route::post('delete/{endereco:uid}', [EnderecoController::class, 'delete'])->name('endereco-delete');
Route::get('check', [EnderecoController::class, 'check'])->name('endereco-check');
