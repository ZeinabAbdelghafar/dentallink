<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_id',
        'customer_name',
        'customer_email',
        'customer_mobile',
        'amount',
        'currency',
        'status'
    ];
}
