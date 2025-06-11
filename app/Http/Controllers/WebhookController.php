<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use DavidMaximous\Fawaterak\Classes\FawaterakVerify;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook received:', $request);

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
