<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZipController;
use App\Http\Controllers\FileController;

// =========================================================
// WELCOME PAGE
// =========================================================
Route::get('/', function () {
    return view('welcome');
});

// =========================================================
// ZIP MANAGEMENT ROUTES
// =========================================================

// Dashboard — available files + download history
Route::get('/zip', [ZipController::class, 'index'])->name('zip.index');

// Direct form submit se ZIP download (password support saathe)
Route::get('/zip/download', [ZipController::class, 'downloadZip'])->name('zip.download');

// AJAX se token-based shared download link generate karo
Route::post('/zip/download-ajax', [ZipController::class, 'downloadZipAjax'])->name('zip.download.ajax');

// Email par download link moko (48hr expiry)
Route::post('/zip/email', [ZipController::class, 'emailZipLink'])->name('zip.email');

// Shared token se ZIP download karo (24hr expiry check saathe)
Route::get('/zip/download-shared/{token}', [ZipController::class, 'downloadSharedZip'])->name('zip.download.shared');

// Multi-folder ZIP — puri folder recursive ZIP karo
Route::post('/zip/folder', [ZipController::class, 'downloadFolderZip'])->name('zip.download.folder');

// =========================================================
// BACKGROUND QUEUE JOB ROUTES
// =========================================================

// Large files mate background queue ma ZIP job dispatch karo
Route::post('/zip/queue', [ZipController::class, 'queueZipJob'])->name('zip.queue');

// Job status check karo — frontend polling use karse
Route::get('/zip/job/{id}/status', [ZipController::class, 'checkJobStatus'])->name('zip.job.status');

// Completed background job ni ZIP file download karo
Route::get('/zip/job/{id}/download', [ZipController::class, 'downloadJobZip'])->name('zip.job.download');

// =========================================================
// FILE MANAGEMENT ROUTES
// =========================================================

// File upload karo (max 10MB)
Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');

// File delete karo
Route::delete('/files/delete/{filename}', [FileController::class, 'delete'])->name('files.delete');

// File preview — text files content, images direct return
Route::get('/files/preview/{filename}', [FileController::class, 'preview'])->name('files.preview');

Route::get('/files/list', [FileController::class, 'list'])->name('files.list');