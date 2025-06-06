<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unique_string',
        'expire_at',
    ];

    protected $dates = [
        'expire_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}