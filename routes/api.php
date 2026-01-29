<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/materials', [\App\Http\Controllers\Api\MaterialController::class, 'index']);
Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);

// Авторизация через Telegram
Route::post('/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
Route::get('/auth/user', [\App\Http\Controllers\Api\AuthController::class, 'user']);
Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Api\AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\AdminOrderController::class, 'show']);
    Route::put('/orders/{id}', [\App\Http\Controllers\Api\AdminOrderController::class, 'update']);
});
