<?php

use App\Http\Controllers\CalcRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::apiResource('calc-requests', CalcRequestController::class);
});
