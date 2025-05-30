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
        $user = $request->user();
        if (!$user || !$user->email) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $email = $user->email;
        $productId = $request->input('productId');
        $qtyRaw = $request->input('qty');
        $qty = (int) $qtyRaw;

        if (!$productId || !$qty) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if ($product->stock < 1) {
            return response()->json(['error' => 'Product is out of stock'], 400);
        }

        if ($qty > $product->stock) {
            return response()->json(['error' => 'Not enough stock'], 400);
        }
        if ($qty <= 0) {
            return response()->json(['error' => 'Invalid quantity'], 400);
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
                return response()->json(['error' => 'Not enough stock for the requested quantity'], 400);
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
        $user = $request->user();
        if (!$user || !$user->email) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $email = $user->email;
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
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $cart = Cart::where('email', $email)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $item = $cart->items()->where('productId', $productId)->first();
        if (!$item) {
            return response()->json(['error' => 'Item not found in cart'], 404);
        }

        if ($qty > $item->stock) {
            return response()->json(['error' => 'Not enough stock for the requested quantity'], 400);
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
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $cart = Cart::where('email', $email)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $cart->items()->where('productId', $productId)->delete();
        $cart->refresh();

        return response()->json($cart->items);
    }
}
