<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;

Route::post('/places', [PlaceController::class, 'store'])
    ->middleware(\App\Http\Middleware\ApiTokenAuth::class);
