<?php

use App\Http\Controllers\CalcRequestController;
use App\Http\Controllers\SelectedPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::apiResource('calc-requests', CalcRequestController::class);
    Route::apiResource('selected-payments', SelectedPaymentController::class);

});
