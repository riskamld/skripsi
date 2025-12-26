<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;

Route::get('/places', [PlaceController::class, 'index'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);

Route::post('/places', [PlaceController::class, 'store'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);
