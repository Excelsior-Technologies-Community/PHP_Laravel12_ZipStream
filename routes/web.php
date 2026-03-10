<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZipController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/zip', [ZipController::class, 'index']);
Route::get('/zip/download', [ZipController::class, 'downloadZip'])->name('zip.download');