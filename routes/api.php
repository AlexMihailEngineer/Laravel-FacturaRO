<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\InvoiceStoreController;
use App\Http\Controllers\Api\V1\InvoiceStatusController;
use App\Http\Controllers\Api\V1\InvoiceDownloadController;

Route::prefix('v1')->group(function () {
    // 1. Ingestion endpoint
    Route::post('/facturi', InvoiceStoreController::class);

    // 2. Polling endpoint
    Route::get('/facturi/{id}/status', InvoiceStatusController::class);

    // 3. Download endpoint
    Route::get('/facturi/{id}/download', InvoiceDownloadController::class)
        ->name('api.v1.facturi.download'); // Named route makes generating URLs easier
});
