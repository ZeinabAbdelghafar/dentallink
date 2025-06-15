<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;


Route::get('/payment-redirect/{status?}', function ($status = null) {
    return response()->json([
        'status' => $status ?? 'unknown',
        'invoice_id' => request()->query('invoice_id'),
    ]);
})->name('payment-redirect');
