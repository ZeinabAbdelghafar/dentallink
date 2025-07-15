<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

class ProductBackInStock extends Mailable
{
    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function build()
    {
        Log::info("Sending back in stock notification for product ID: {$this->product->id}");
        return $this->subject("🔔 {$this->product->title} is back in stock!")
            ->markdown('emails.product.back_in_stock')
            ->with([
                'product' => $this->product,
                'url' => url('/api/products/' . $this->product->id)
            ]);
    }
}
