<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PlaceController;
use App\Http\Controllers\Web\ScrapeLogController;
use App\Http\Controllers\Web\ApiTokenController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Places CRUD routes
Route::resource('places', PlaceController::class);

// Scrape Logs routes
Route::get('/scrape-logs', [ScrapeLogController::class, 'index'])->name('scrape-logs.index');
Route::get('/scrape-logs/{scrapeLog}', [ScrapeLogController::class, 'show'])->name('scrape-logs.show');

// API Tokens routes
Route::resource('api-tokens', ApiTokenController::class)->except(['create', 'edit']);
Route::post('/api-tokens/{id}/toggle-status', [ApiTokenController::class, 'toggleStatus'])->name('api-tokens.toggle-status');
Route::post('/api-tokens/{id}/regenerate', [ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');
