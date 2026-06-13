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
use App\Http\Controllers\Web\MapController;
use App\Http\Controllers\Web\ScraperController;
use App\Http\Controllers\Web\WhatsAppController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Scraper
Route::get('/scraper', [ScraperController::class, 'index'])->name('scraper.index');
Route::get('/scraper/stats', [ScraperController::class, 'stats'])->name('scraper.stats');
Route::post('/scraper/start', [ScraperController::class, 'start'])->name('scraper.start');
Route::get('/scraper/log/{jobId}', [ScraperController::class, 'log'])->name('scraper.log');
Route::post('/scraper/rescrape', [ScraperController::class, 'rescrape'])->name('scraper.rescrape');
Route::get('/scraper/rescrape-count', [ScraperController::class, 'rescrapeCount'])->name('scraper.rescrape-count');
Route::get('/scraper/rescrape-progress', [ScraperController::class, 'rescrapeProgress'])->name('scraper.rescrape-progress');
Route::post('/scraper/save-cookies', [ScraperController::class, 'saveCookies'])->name('scraper.save-cookies');
Route::get('/scraper/cookie-status', [ScraperController::class, 'cookieStatus'])->name('scraper.cookie-status');
Route::post('/scraper/check-cookies', [ScraperController::class, 'checkCookies'])->name('scraper.check-cookies');
Route::get('/map', [MapController::class, 'index'])->name('map.index');

// API Routes for Map (moved from api.php as workaround)
Route::prefix('api/map')->group(function () {
    Route::post('/check-updates', [MapController::class, 'checkUpdates']);
    Route::delete('/delete-category', [MapController::class, 'deleteCategory']);
});

// Language switching removed - using Indonesian by default
Route::get('/market-analysis', [MarketAnalysisController::class, 'index'])->name('market-analysis.index');
Route::get('/market-analysis/supply-demand', [MarketAnalysisController::class, 'supplyDemand'])->name('market-analysis.supply-demand');
Route::get('/market-analysis/category-insights', [MarketAnalysisController::class, 'categoryInsights'])->name('market-analysis.category-insights');
Route::get('/market-analysis/geographic', [MarketAnalysisController::class, 'geographic'])->name('market-analysis.geographic');
Route::get('/market-analysis/price-predictions', [MarketAnalysisController::class, 'pricePredictions'])->name('market-analysis.price-predictions');

// Places CRUD routes
Route::resource('places', PlaceController::class);
Route::post('/places/clear-all', [PlaceController::class, 'clearAll'])->name('places.clear-all');
Route::post('/places/bulk-delete', [PlaceController::class, 'bulkDelete'])->name('places.bulk-delete');

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

// WhatsApp routes
Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
Route::get('/whatsapp/devices', [WhatsAppController::class, 'devices'])->name('whatsapp.devices');
Route::get('/whatsapp/stats', [WhatsAppController::class, 'stats'])->name('whatsapp.stats');
Route::post('/whatsapp/check-wa', [WhatsAppController::class, 'checkWA'])->name('whatsapp.check-wa');
Route::post('/whatsapp/send-outreach', [WhatsAppController::class, 'sendOutreach'])->name('whatsapp.send-outreach');
Route::post('/whatsapp/mark-status/{id}', [WhatsAppController::class, 'markStatus'])->name('whatsapp.mark-status');
Route::post('/whatsapp/save-notes/{id}', [WhatsAppController::class, 'saveNotes'])->name('whatsapp.save-notes');
Route::get('/whatsapp/target-list', [WhatsAppController::class, 'targetList'])->name('whatsapp.target-list');
Route::post('/whatsapp/templates', [WhatsAppController::class, 'storeTemplate'])->name('whatsapp.templates.store');
Route::put('/whatsapp/templates/{template}', [WhatsAppController::class, 'updateTemplate'])->name('whatsapp.templates.update');
Route::delete('/whatsapp/templates/{template}', [WhatsAppController::class, 'destroyTemplate'])->name('whatsapp.templates.destroy');
Route::post('/whatsapp/templates/{template}/toggle', [WhatsAppController::class, 'toggleTemplate'])->name('whatsapp.templates.toggle');

// Database Tools routes
Route::get('/database', [DatabaseController::class, 'index'])->name('database.index');
Route::post('/database/export/sql', [DatabaseController::class, 'exportSql'])->name('database.export.sql');
Route::post('/database/export/csv', [DatabaseController::class, 'exportCsv'])->name('database.export.csv');
Route::post('/database/export/json', [DatabaseController::class, 'exportJson'])->name('database.export.json');
Route::post('/database/import/sql', [DatabaseController::class, 'importSql'])->name('database.import.sql');
Route::post('/database/import/csv', [DatabaseController::class, 'importCsv'])->name('database.import.csv');
Route::get('/database/download/{filename}', [DatabaseController::class, 'download'])->name('database.download');
Route::delete('/database/files/{filename}', [DatabaseController::class, 'deleteFile'])->name('database.delete-file');

// Panduan
Route::view('/panduan', 'panduan.index')->name('panduan.index');
