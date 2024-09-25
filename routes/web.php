<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\MarketController;

Route::get('/market', [MarketController::class, 'index']);
Route::post('/market', [MarketController::class, 'addMarket']);
Route::delete('/market/{id}', [MarketController::class, 'delete']);
