<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingFee;
use App\Models\User;
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
            'address_line' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'phone' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::where('email', $user->email)->first();

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

        $city = ucfirst(strtolower($validated['city']));
        $fee = ShippingFee::where('governorate', $city)->first()?->fee;
        $fee = $fee ?? 100;
        $total += $fee;

        $order = Order::create([
            'user_id' => $user ? $user->id : null,
            'customer_first_name' => $user->username ?? '',
            'customer_last_name' => $user->last_name ?? '',
            'customer_email' => $cart->email,
            'customer_phone' => $validated['phone'],
            'total' => $total,
            'items' => json_encode($items),
            'payment_reference' => 'cash_on_delivery',
            'address_line' => $validated['address_line'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
        ]);

        foreach ($items as $item) {
            $product = Product::find($item['id']);
            $product->stock -= $item['qty'];
            $product->save();
        }

        $cart->items()->delete();
        $cart->delete();

        return response()->json([
            'order_id' => $order->id,
            'total' => $total,
            'shipping_fee' => $fee,
            'items' => $items,
            'message' => 'Order created successfully with cash on delivery payment method.'
        ]);
    }

    public function getOrders(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->role === 'admin') {
            $orders = Order::all();
        } else {
            $orders = Order::where('user_id', $user->id)->get();
        }

        return response()->json($orders);
    }

    public function markCashOnDeliveryPaid(Request $request, $orderId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized: User not authenticated'], 401);
        }
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized: Only admin can mark cash on delivery orders as paid'], 401);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->payment_reference !== 'cash_on_delivery') {
            return response()->json(['error' => 'Order is not cash on delivery'], 400);
        }

        $order->paid = true;
        $order->paid_at = now();
        $order->save();

        return response()->json(['message' => 'Order marked as paid', 'order' => $order]);
    }

    public function getOrderDetails($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json($order);
    }
}
