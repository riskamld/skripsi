<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PlaceController;
use App\Http\Controllers\Web\ScrapeLogController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Places CRUD routes
Route::resource('places', PlaceController::class);

// Scrape Logs routes
Route::get('/scrape-logs', [ScrapeLogController::class, 'index'])->name('scrape-logs.index');
Route::get('/scrape-logs/{scrapeLog}', [ScrapeLogController::class, 'show'])->name('scrape-logs.show');
