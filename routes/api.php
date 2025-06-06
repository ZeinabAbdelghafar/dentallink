<?php

use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\RequireAuth;
use App\Http\Middleware\CheckUser;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsOwner;

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

Route::prefix('auth')->group(function () {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/verify/{id}/{uuid}', [AuthController::class, 'verify']);
});

Route::middleware([CheckUser::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'get']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});

Route::middleware([RequireAuth::class])->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('/count', [UserController::class, 'countUsers']);
        
        Route::middleware([IsAdmin::class])->group(function () {
            Route::get('/', [UserController::class, 'getUsers']);
            Route::delete('/{id}', [UserController::class, 'deleteUser']);
        });
        
        Route::middleware([IsOwner::class])->group(function () {
            Route::get('/{id}', [UserController::class, 'getUser']);
        });
        
        Route::post('/ResetPassword', [UserController::class, 'resetLink']);
        Route::put('/ResetPassword/{id}/', [UserController::class, 'resetLogic']);
        Route::put('/email', [UserController::class, 'emailUpdate']);
    });
    
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'get']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store'])->middleware(['auth:api', IsAdmin::class]);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update'])->middleware(['auth:api', IsAdmin::class]);
    Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware(['auth:api', IsAdmin::class]);

    Route::post('/{id}/rating', [ProductController::class, 'addRating'])->middleware('auth:api');
    Route::get('/{id}/rating', [ProductController::class, 'getRating']);

    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/count', [ProductController::class, 'count'])->middleware(['auth:api', IsAdmin::class]);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/count', [CategoryController::class, 'count']);
    Route::post('/', [CategoryController::class, 'store'])->middleware(['auth:api', IsAdmin::class]);
    Route::get('/{id}', [CategoryController::class, 'show']);
});
