<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProductPriceController;

Route::get('/places', [PlaceController::class, 'index'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::post('/places', [PlaceController::class, 'store'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::delete('/places/delete-today', [PlaceController::class, 'deleteScrapedToday'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::get('/places/needs-rescrape', [PlaceController::class, 'needsRescrape'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::patch('/places/{id}', [PlaceController::class, 'update'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

// Product Prices API (removed to avoid route name conflicts)
// API functionality handled through web routes with authentication

// AI Chat API Routes (no CSRF needed)
Route::prefix('ai-chat')->group(function () {
    Route::get('/context', [App\Http\Controllers\Web\AiChatController::class, 'getDatabaseContext']);
    Route::post('/query', [App\Http\Controllers\Web\AiChatController::class, 'processQuery']);
});

// WA Webhook — dipanggil wa-api saat ada pesan masuk (dari localhost, tanpa auth)
Route::post('/wa-webhook', [App\Http\Controllers\Api\WaWebhookController::class, 'handle']);

// Map API Routes moved to routes/web.php as workaround for VPS deployment
