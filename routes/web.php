<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PlaceController;
use App\Http\Controllers\Web\ScrapeLogController;
use App\Http\Controllers\Web\ApiTokenController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Places CRUD routes
Route::resource('places', PlaceController::class);
Route::delete('/places/clear-all', [PlaceController::class, 'clearAll'])->name('places.clear-all');

// Scrape Logs routes
Route::resource('scrape-logs', ScrapeLogController::class)->except(['create', 'store', 'edit', 'update']);
Route::delete('/scrape-logs/clear-all', [ScrapeLogController::class, 'clearAll'])->name('scrape-logs.clear-all');

// API Tokens routes
Route::resource('api-tokens', ApiTokenController::class)->except(['create', 'edit']);
Route::post('/api-tokens/{id}/toggle-status', [ApiTokenController::class, 'toggleStatus'])->name('api-tokens.toggle-status');
Route::post('/api-tokens/{id}/regenerate', [ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');
