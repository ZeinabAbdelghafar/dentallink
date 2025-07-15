<?php

use App\Http\Controllers\CartController;
use App\Mail\ProductBackInStock;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


// Route::get('/payment-redirect/{status?}', function ($status = null) {
//     return response()->json([
//         'status' => $status ?? 'unknown',
//         'invoice_id' => request()->query('invoice_id'),
//     ]);
// })->name('payment-redirect');


Route::get('/payment-redirect/{status?}', function ($status = null) {
    $invoiceId = request()->query('invoice_id');
    $invoiceKey = Order::where('invoice_id', $invoiceId);
    if ($invoiceKey->exists()) {
        $invoiceKey = $invoiceKey->first()->invoice_key;
    } else {
        $invoiceKey = null;
    }

    Log::info("Redirecting to payment with status: {$status}, invoice_id: {$invoiceId}, invoice_key: {$invoiceKey}");
    if (!$invoiceId || !$invoiceKey) {
        return response()->json(['error' => 'Missing invoice_id or invoice_key'], 400);
    }

    $invoiceUrl = "https://staging.fawaterk.com/invoice/{$invoiceId}/{$invoiceKey}";

    return redirect()->away($invoiceUrl);
})->name('payment-redirect');


Route::get('/test-mail', function () {
    $product = \App\Models\Product::first();
    Mail::to('engya306@email.com')->send(new ProductBackInStock($product));
    return 'Mail sent!';
});


Route::get('/test-stock-restock/{id}', function ($id) {
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
    }
    $product->stock = 5;
    $product->save();

    return response()->json([
        'message' => "Stock updated",
        'product_id' => $id,
        'new_stock' => $product->stock
    ]);
});
