<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'total',
        'items',
        'invoice_id',
        'invoice_key',
        'payment_method',
        'payment_reference',
        'paid',
        'paid_at',
        'address_line',
        'city',
        'state',
        'postal_code',
        'country',
    ];


    protected $casts = [
        'items' => 'array',
        'paid' => 'boolean',
        'paid_at' => 'datetime',
    ];
}
