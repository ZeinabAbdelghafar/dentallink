<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use DavidMaximous\Fawaterak\Classes\FawaterakVerify;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info('Received webhook request:');
            Log::info('Request data:', $request->all());
            $verifier = new FawaterakVerify();
            $result = $verifier->verifyCallback($request);
            if (!$result) {
                Log::error('verifyCallback() returned null');
                return response()->json(['error' => 'Invalid verification result'], 500);
            }


            Log::info('Full verification result:', ['result' => $result]);

            if (!isset($result['success']) || !$result['success']) {
                Log::warning('Fawaterak verification failed.', $request->all());
                return response()->json(['error' => 'Verification failed'], 403);
            }

            $payload = json_decode($result['payload'], true);
            Log::info('Decoded payload:', $payload);

            $orderId = $payload[0]['order_id'] ?? null;
            Log::info('Order ID from decoded payload:', ['order_id' => $orderId]);

            if (!$orderId) return response()->json(['error' => 'No order ID'], 400);

            $order = Order::find($orderId);
            Log::info('Order retrieved:', ['order' => $order]);
            if (!$order || $order->paid) return response()->json(['message' => 'Already paid or invalid order'], 200);

            DB::beginTransaction();
            try {
                $order->update([
                    'paid' => true,
                    'invoice_id' => $result['invoice_id'] ?? null,
                    'paid_at' => now(),
                    'payment_reference' => $result['payment_method']
                ]);
                Log::info('Order updated successfully:', ['order_id' => $order->id]);

                $items = json_decode($order->items, true);
                Log::info('Items retrieved:', ['items' => $items]);
                foreach ($items as $item) {
                    Product::where('id', $item['id'])->decrement('stock', $item['qty']);
                }

                Log::info('Stock decremented for items in order:', ['items' => $items]);
                DB::commit();
                Log::info('Transaction committed successfully');
                return response()->json(['message' => 'Order updated and stock adjusted'], 200);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Error processing payment webhook', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Internal error'], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Error in webhook handler', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Unexpected error'], 500);
        }
    }
}
