<?php

use App\Http\Controllers\PasienHewanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pasien-hewan', [PasienHewanController::class, 'index']);
Route::post('/pasien-hewan', [PasienHewanController::class, 'store']);