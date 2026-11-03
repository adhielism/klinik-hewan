<?php

use App\Http\Controllers\PasienHewanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pasien-hewan');
});

Route::get('/pasien-hewan', [PasienHewanController::class, 'index']);
Route::post('/pasien-hewan', [PasienHewanController::class, 'store']);
Route::get('/pasien-hewan/{id}/edit', [PasienHewanController::class, 'edit'])->whereNumber('id');
Route::put('/pasien-hewan/{id}', [PasienHewanController::class, 'update'])->whereNumber('id');
Route::delete('/pasien-hewan/{id}', [PasienHewanController::class, 'destroy'])->whereNumber('id');