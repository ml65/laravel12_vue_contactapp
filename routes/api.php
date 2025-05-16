<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;

// Защищаем маршруты contacts с помощью Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('contacts', ContactController::class);
});

