<?php

use App\Http\Controllers\BukuController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/buku');

Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');
Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])
    ->whereNumber('buku')
    ->name('buku.edit');
Route::put('/buku/{buku}', [BukuController::class, 'update'])
    ->whereNumber('buku')
    ->name('buku.update');
Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])
    ->whereNumber('buku')
    ->name('buku.destroy');
