<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\FawaterkService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $fawaterk;

    public function __construct(FawaterkService $fawaterk)
    {
        $this->fawaterk = $fawaterk;
    }

    public function createInvoice(Request $request)
    {
        $validated = $request->validate([
            'customer_name'   => 'required|string',
            'customer_email'  => 'nullable|email',
            'customer_mobile' => 'required|string',
            'amount'          => 'required|numeric',
        ]);

        $invoice = $this->fawaterk->createInvoice([
            'customer_name'   => $validated['customer_name'],
            'customer_email'  => $validated['customer_email'],
            'customer_mobile' => $validated['customer_mobile'],
            'amount'          => $validated['amount'],
            'currency'        => 'EGP',
            'redirect_url'    => route('payment.success'),
        ]);

        if (!isset($invoice['url'])) {
            return response()->json(['error' => 'Invoice creation failed'], 500);
        }

        Order::create([
            'invoice_id'      => $invoice['invoice_id'] ?? null,
            'customer_name'   => $validated['customer_name'],
            'customer_email'  => $validated['customer_email'],
            'customer_mobile' => $validated['customer_mobile'],
            'amount'          => $validated['amount'],
            'status'          => 'pending',
        ]);

        return response()->json(['url' => $invoice['url']], 200);
    }

    public function webhook(Request $request)
    {
        Log::info('Fawaterk Webhook:', $request->all());

        $invoiceId = $request->input('invoice_id');
        $status    = $request->input('payment_status');

        if (!$invoiceId || !$status) {
            return response()->json(['message' => 'Invalid data'], 400);
        }

        $order = Order::where('invoice_id', $invoiceId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($status === 'paid') {
            $order->status = 'paid';
        } elseif ($status === 'failed') {
            $order->status = 'failed';
        }

        $order->save();

        return response()->json(['message' => 'Order updated'], 200);
    }

    public function success()
    {
        return response()->json(['message' => 'Payment completed!']);
    }
}
