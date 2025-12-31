<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PlaceController;
use App\Http\Controllers\Web\ScrapeLogController;
use App\Http\Controllers\Web\ApiTokenController;
use App\Http\Controllers\Web\MarketAnalysisController;
use App\Http\Controllers\Web\ProductPriceController;
use App\Http\Controllers\Web\DatabaseController;
use App\Http\Controllers\Web\AiChatController;
use App\Http\Controllers\Web\LanguageController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Language switching route (placed early to avoid conflicts)
Route::post('/language', [LanguageController::class, 'switchLanguage'])->name('language.switch');
Route::get('/market-analysis', [MarketAnalysisController::class, 'index'])->name('market-analysis.index');
Route::get('/market-analysis/supply-demand', [MarketAnalysisController::class, 'supplyDemand'])->name('market-analysis.supply-demand');
Route::get('/market-analysis/category-insights', [MarketAnalysisController::class, 'categoryInsights'])->name('market-analysis.category-insights');
Route::get('/market-analysis/geographic', [MarketAnalysisController::class, 'geographic'])->name('market-analysis.geographic');
Route::get('/market-analysis/price-predictions', [MarketAnalysisController::class, 'pricePredictions'])->name('market-analysis.price-predictions');

// Places CRUD routes
Route::resource('places', PlaceController::class);
Route::post('/places/clear-all', [PlaceController::class, 'clearAll'])->name('places.clear-all');

// Scrape Logs routes
Route::post('/scrape-logs/clear-all', [ScrapeLogController::class, 'clearAll'])->name('scrape-logs.clear-all');
Route::resource('scrape-logs', ScrapeLogController::class)->except(['create', 'store', 'edit', 'update']);

// API Tokens routes
Route::resource('api-tokens', ApiTokenController::class)->except(['edit']);
Route::post('/api-tokens/{id}/toggle-status', [ApiTokenController::class, 'toggleStatus'])->name('api-tokens.toggle-status');
Route::post('/api-tokens/{id}/regenerate', [ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');

// Product Prices routes
Route::resource('product-prices', ProductPriceController::class);
Route::post('/product-prices/bulk-delete', [ProductPriceController::class, 'bulkDelete'])->name('product-prices.bulk-delete');
Route::post('/product-prices/clear-all', [ProductPriceController::class, 'clearAll'])->name('product-prices.clear-all');

// Database Tools routes
Route::get('/database', [DatabaseController::class, 'index'])->name('database.index');
Route::post('/database/export/sql', [DatabaseController::class, 'exportSql'])->name('database.export.sql');
Route::post('/database/export/csv', [DatabaseController::class, 'exportCsv'])->name('database.export.csv');
Route::post('/database/export/json', [DatabaseController::class, 'exportJson'])->name('database.export.json');
Route::post('/database/import/sql', [DatabaseController::class, 'importSql'])->name('database.import.sql');
Route::post('/database/import/csv', [DatabaseController::class, 'importCsv'])->name('database.import.csv');
Route::get('/database/download/{filename}', [DatabaseController::class, 'download'])->name('database.download');
Route::delete('/database/files/{filename}', [DatabaseController::class, 'deleteFile'])->name('database.delete-file');
