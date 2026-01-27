<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/materials', [\App\Http\Controllers\Api\MaterialController::class, 'index']);
Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
