<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProductPriceController;

Route::get('/places', [PlaceController::class, 'index'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::post('/places', [PlaceController::class, 'store'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

// Product Prices API (removed to avoid route name conflicts)
// API functionality handled through web routes with authentication

// AI Chat API Routes (no CSRF needed)
Route::prefix('ai-chat')->group(function () {
    Route::get('/context', [App\Http\Controllers\Web\AiChatController::class, 'getDatabaseContext']);
    Route::post('/query', [App\Http\Controllers\Web\AiChatController::class, 'processQuery']);
});
