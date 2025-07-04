<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ShippingFee;
use DavidMaximous\Fawaterak\Classes\FawaterakPayment;
use Illuminate\Support\Facades\Log;
use DavidMaximous\Fawaterak\Classes\FawaterakVerify;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FawaterakController extends Controller
{

    public function pay(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'payment_method_id' => 'required|string',
            'address_line' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $cart = Cart::with('items')->where('email', $user->email)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart not found or empty.'], 404);
        }

        $items = $cart->items->map(fn($item) => [
            'id' => $item->productId,
            'name' => $item->name,
            'price' => $item->price,
            'qty' => $item->qty,
        ])->toArray();

        $total = collect($items)->reduce(function ($sum, $item) {
            return $sum + ($item['price'] * $item['qty']);
        }, 0);

        $city = ucfirst(strtolower($validated['city']));
        $fee = ShippingFee::where('governorate', $city)->first()?->fee;
        $fee = $fee ?? 100;
        $total += $fee;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_first_name' => $validated['first_name'],
                'customer_last_name' => $validated['last_name'],
                'customer_email' => $user->email,
                'customer_phone' => $validated['phone'],
                'total' => $total,
                'items' => json_encode($items),
                'address_line' => $validated['address_line'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'country' => $validated['country'] ?? null,
            ]);

            $fawaterak = new FawaterakPayment();

            $result = $fawaterak->pay(
                amount: $order->total,
                first_name: $order->customer_first_name,
                last_name: $order->customer_last_name,
                user_email: $order->customer_email,
                user_phone: $order->customer_phone,
                method: $validated['payment_method_id'],
                item_name: 'Order #' . $order->id,
                quantity: 1,
                currency: 'EGP',
                language: 'en',
                payload: ['order_id' => $order->id]
            );

            if ($result['status'] !== 'success') {
                DB::rollBack();
                return response()->json(['error' => $result['message']], 422);
            }

            $order->update([
                'invoice_key' => $result['invoice_key'],
                'invoice_id' => $result['invoice_id'],
                'payment_method' => $validated['payment_method_id'],
            ]);

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully. Please complete the payment.',
                'payment_link' => $result['link'],
                'order_id' => $order->id,
                'shipping_fee' => $fee,
                'order' => $order
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Payment processing failed.',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function handle(Request $request)
    {
        Log::info('Webhook received:', $request->all());

        $verifier = new FawaterakVerify();
        $result = $verifier->verifyCallback($request);

        if (!isset($result['success']) || !$result['success']) {
            Log::warning('Fawaterak verification failed.', $request->all());
            return response()->json(['error' => 'Verification failed'], 403);
        }

        $orderId = $result['payload']['order_id'] ?? null;
        if (!$orderId) return response()->json(['error' => 'No order ID'], 400);

        $order = Order::find($orderId);
        if (!$order || $order->paid) return response()->json(['message' => 'Already paid or invalid order'], 200);

        $order->update([
            'paid' => true,
            'paid_at' => now(),
            'payment_reference' => $result['payment_method']
        ]);

        return response()->json(['message' => 'Order updated'], 200);
    }
}
