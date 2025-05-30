<?php

use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WishlistController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->apiResource('cart', CartController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggleFavorite']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::put('/wishlist/{productId}', [WishlistController::class, 'update']);
    Route::delete('/wishlist', [WishlistController::class, 'destroy']);
});
