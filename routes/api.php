<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificatesController;

Route::get('/certificates', [CertificatesController::class, 'index']);
Route::get('/certificates/{id}', [CertificatesController::class, 'show']);
Route::post('/certificates', [CertificatesController::class, 'store']);
Route::get('/certificates/{id}/pdf', [CertificatesController::class, 'downloadPdf']);
Route::post('/certificates/verify', [CertificatesController::class, 'verify']);
Route::get('/wallet/{address}/certificates', [CertificatesController::class, 'getWalletNFTs']);
Route::post('/certificates/sync', [CertificatesController::class, 'sync']);
Route::post('/certificates/{id}/confirm', [CertificatesController::class, 'confirm']);





