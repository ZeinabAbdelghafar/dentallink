<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class orderController extends Controller
{
    public function create(Request $request)
    {
        dd($request->all());
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'total' => 'required|numeric|min:0.01',
            'items' => 'nullable|array',
        ]);

        dd($validated);
        $order = Order::create([
            'customer_first_name' => $validated['first_name'],
            'customer_last_name' => $validated['last_name'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'],
            'total' => $validated['total'],
            'items' => json_encode($validated['items']),
        ]);

        return response()->json(['order_id' => $order->id], 201);
    }


    
    // cash on delivery
    public function createWithCart(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $cart = \App\Models\Cart::where('email', $validated['email'])->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart not found or empty.'], 404);
        }

        $items = $cart->items->map(function ($item) {
            return [
                'id' => $item->productId,
                'name' => $item->name,
                'price' => $item->price,
                'qty' => $item->qty,
            ];
        })->toArray();

        $total = collect($items)->reduce(function ($sum, $item) {
            return $sum + ($item['price'] * $item['qty']);
        }, 0);

        $user = \App\Models\User::where('email', $cart->email)->first();

        $order = Order::create([
            'user_id' => $user ? $user->id : null,
            'customer_first_name' => $user->username ?? '',
            'customer_last_name' => $user->last_name ?? '',
            'customer_email' => $cart->email,
            'customer_phone' => $user->phone ?? '',
            'total' => $total,
            'items' => json_encode($items),
        ]);

        foreach ($items as $item) {
            $product = \App\Models\Product::find($item['id']);
            $product->stock -= $item['qty'];
            $product->save();
        }

        $cart->items()->delete();
        $cart->delete();

        return response()->json([
            'order_id' => $order->id,
            'total' => $total
        ]);
    }
}
