<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceController;

Route::prefix('invoices')->group(function () {
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::post('/', [InvoiceController::class, 'store']);
    Route::post('/{id}/send', [InvoiceController::class, 'send']);
});
