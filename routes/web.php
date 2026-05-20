<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZipController;
use App\Http\Controllers\FileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/zip', [ZipController::class, 'index'])->name('zip.index');
Route::get('/zip/download', [ZipController::class, 'downloadZip'])->name('zip.download');
Route::post('/zip/download-ajax', [ZipController::class, 'downloadZipAjax'])->name('zip.download.ajax');
Route::post('/zip/email', [ZipController::class, 'emailZipLink'])->name('zip.email');
Route::get('/zip/download-shared/{token}', [ZipController::class, 'downloadSharedZip'])->name('zip.download.shared');

// File management routes
Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
Route::delete('/files/delete/{filename}', [FileController::class, 'delete'])->name('files.delete');
Route::get('/files/preview/{filename}', [FileController::class, 'preview'])->name('files.preview');
Route::get('/files/list', [FileController::class, 'list'])->name('files.list');