<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $email = $request->user()->email;
        $productId = $request->input('productId');
        $qtyRaw = $request->input('qty');
        $qty = (int) $qtyRaw;

        if (!$productId || !$qty) {
            abort(400, 'Invalid request');
        }

        $product = Product::find($productId);
        if (!$product) {
            abort(404, 'Invalid Product ID');
        }

        if ($product->stock < 1) {
            abort(400, 'Out of stock');
        }

        if ($qty > $product->stock) {
            abort(400, 'Not enough stock');
        }

        $productToCart = [
            'productId' => $product->id,
            'name' => $product->title,
            'price' => $product->price,
            'qty' => $qty,
            'img' => $product->image[0] ?? null,
            'stock' => $product->stock,
        ];

        $cart = Cart::firstOrCreate(['email' => $email]);

        $existingItem = $cart->items()->where('productId', $product->id)->first();

        if ($existingItem) {
            $existingItem->qty += $qty;
            $existingItem->stock = $product->stock;

            if ($existingItem->qty > $existingItem->stock) {
                abort(400, 'Not enough stock');
            }

            $existingItem->save();
        } else {
            $cart->items()->create($productToCart);
        }

        $cart->refresh();

        if (!$request->input('reorder')) {
            return response()->json($cart->items);
        } else {
            return $cart->items;
        }
    }

    public function get(Request $request)
    {
        $email = $request->user()->email;
        $cart = Cart::with('items')->where('email', $email)->first();

        if (!$cart) {
            return response()->json([]);
        }

        return response()->json($cart->items);
    }

    public function update(Request $request)
    {
        $email = $request->user()->email;
        $productId = $request->input('productId');
        $qty = $request->input('qty');

        if (!$productId || is_null($qty)) {
            abort(400, 'Invalid request');
        }

        $cart = Cart::where('email', $email)->first();
        if (!$cart) {
            abort(404, 'Cart not found');
        }

        $item = $cart->items()->where('productId', $productId)->first();
        if (!$item) {
            abort(404, 'Product not found in cart');
        }

        if ($qty > $item->stock) {
            abort(400, 'Not enough stock');
        }

        if ($qty <= 0) {
            $item->delete();
        } else {
            $item->qty = $qty;
            $item->save();
        }

        $cart->refresh();
        return response()->json($cart->items);
    }

    public function destroy(Request $request)
    {
        $email = $request->user()->email;
        $productId = $request->input('productId');

        if (!$productId) {
            abort(400, 'Invalid request');
        }

        $cart = Cart::where('email', $email)->first();
        if (!$cart) {
            abort(404, 'Cart not found');
        }

        $cart->items()->where('productId', $productId)->delete();
        $cart->refresh();

        return response()->json($cart->items);
    }
}
