<?php

namespace App\Observers;

use App\Mail\ProductBackInStock;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProductObserver
{
    public function updated(Product $product)
    {
        Log::info("Product updated: {$product->id}, stock: {$product->stock}");
        if ($product->getOriginal('stock') <= 0 && $product->stock > 0) {
            $wishlistItems = WishlistItem::where('productId', $product->id)
                ->whereNull('notified_at')
                ->with('wishlist')
                ->get();

            Log::info("✅ Found " . $wishlistItems->count() . " wishlist items for product {$product->id}");

            foreach ($wishlistItems as $item) {
                $email = $item->wishlist->email;
                if ($email) {
                    Mail::to($email)->queue(new ProductBackInStock($product));

                    $item->notified_at = now();
                    $item->save();
                }
            }
        }
    }
}
