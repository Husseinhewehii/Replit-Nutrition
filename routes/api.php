<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\PortionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('foods', FoodController::class);
    Route::apiResource('portions', PortionController::class)->except(['update']);
    
    Route::get('/daily-totals', [PortionController::class, 'dailyTotals']);
    Route::post('/portions/quick-add', [PortionController::class, 'quickAdd']);
});
