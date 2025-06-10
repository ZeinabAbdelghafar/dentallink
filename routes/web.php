<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;



Route::get('/payment-redirect', function () {
    return response()->json([
        'message' => 'Redirected from Fawaterak',
    ]);
})->name('payment-redirect');
