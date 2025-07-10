<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the cart items.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->email) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::with('items')->where('email', $user->email)->first();

        return response()->json($cart?->items ?? []);
    }

    /**
     * Show the form for creating a new cart item.
     */
    public function create()
    {
        return response()->json(['message' => 'Form for adding cart item'], 200);
    }

    /**
     * Store a newly created cart item in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->email) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $productId = $request->input('productId');
        $qty = (int) $request->input('qty');

        if (!$productId || $qty <= 0) {
            return response()->json(['error' => 'Invalid product or quantity'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if ($product->stock < $qty) {
            return response()->json([
                'error' => 'Not enough stock',
                'message' => "Only {$product->stock} units available in stock for '{$product->title}'"
            ], 400);
        }

        $cart = Cart::firstOrCreate(['email' => $user->email]);

        $cartItem = $cart->items()->where('productId', $productId)->first();

        if ($cartItem) {
            $newQty = $cartItem->qty + $qty;

            if ($newQty > $product->stock) {
                return response()->json(['error' => 'Not enough stock for the requested quantity'], 400);
            }

            $cartItem->qty = $newQty;
            $cartItem->stock = $product->stock;
            $cartItem->save();
        } else {
            $cart->items()->create([
                'productId' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'qty' => $qty,
                'img' => $product->image[0] ?? null,
                'stock' => $product->stock,
            ]);
        }

        $cart->refresh();

        return response()->json($cart->items);
    }

    /**
     * Display the specified cart item (by product ID).
     */
    public function show(string $productId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::with('items')->where('email', Auth::user()->email)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $item = $cart->items()->where('productId', $productId)->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found in cart'], 404);
        }

        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return response()->json(['message' => 'Form for editing cart item'], 200);
    }

    /**
     * Update the specified cart item.
     */
    public function update(Request $request, string $productId)
    {
        $qty = (int) $request->input('qty');
        $cart = Cart::where('email', Auth::user()->email)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $item = $cart->items()->where('productId', $productId)->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found in cart'], 404);
        }

        if ($qty <= 0) {
            $item->delete();
        } else {
            if ($qty > $item->stock) {
                return response()->json(['error' => 'Not enough stock for the requested quantity'], 400);
            }

            $item->qty = $qty;
            $item->save();
        }

        $cart->refresh();
        return response()->json($cart->items);
    }

    /**
     * Remove the specified cart item from storage.
     */
    public function destroy(string $productId)
    {
        $cart = Cart::where('email', Auth::user()->email)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $item = $cart->items()->where('productId', $productId)->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found in cart'], 404);
        }

        $item->delete();
        $cart->refresh();

        return response()->json([
            'message' => 'Item removed from cart',
            'items' => $cart->items
        ]);
    }
}
