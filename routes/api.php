<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProductPriceController;

Route::get('/places', [PlaceController::class, 'index'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::post('/places', [PlaceController::class, 'store'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

// Product Prices API
Route::apiResource('product-prices', ProductPriceController::class)
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);
