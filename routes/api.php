<?php

use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FawaterakController;
use App\Http\Controllers\HomePageSettingsController;
use App\Http\Controllers\orderController;
use App\Http\Middleware\RequireAuth;
use App\Http\Middleware\CheckUser;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsOwner;
use App\Http\Controllers\WebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware([RequireAuth::class])->apiResource('cart', CartController::class);

Route::middleware([RequireAuth::class])->group(function () {
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



Route::get('/settings/home', [HomePageSettingsController::class, 'show']);
Route::post('/settings/home', [HomePageSettingsController::class, 'update']);


// https://2ae2-41-47-78-82.ngrok-free.app/api/payment/webhook  --> Fawaterk webhook URL
// Route::prefix('payment')->group(function () {
//     Route::post('/create', [PaymentController::class, 'createInvoice']);
//     Route::post('/webhook', [PaymentController::class, 'webhook']);
//     Route::get('/success', [PaymentController::class, 'success'])->name('payment.success');
// });

Route::prefix('orders')->middleware([RequireAuth::class])->group(function () {
    Route::post('/', [orderController::class, 'createWithCart']);
    Route::get('/', [orderController::class, 'getOrders']);
    Route::get('/{id}', [orderController::class, 'getOrderDetails']);
    Route::post('/pay', [FawaterakController::class, 'pay']);
    Route::post('/{orderId}/mark-cash-paid', [orderController::class, 'markCashOnDeliveryPaid'])->middleware([IsAdmin::class]);
});


Route::post('/fawaterak/webhook', [WebhookController::class, 'handle']);
// Route::middleware([RequireAuth::class])->post('/orders/pay', [FawaterakController::class, 'pay']);
