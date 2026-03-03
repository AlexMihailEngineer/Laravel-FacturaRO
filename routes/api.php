<?php

use App\Http\Controllers\Api\V1\InvoiceStoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/facturi', InvoiceStoreController::class);
});
