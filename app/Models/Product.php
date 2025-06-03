<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id',
        'images',
        'stock',
        'ratings',
        'average_rating',
        'total_ratings'
    ];

    protected $casts = [
        'images' => 'array',
        'ratings' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function calculateAverageRating()
    {
        $ratings = $this->ratings ?? ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
        
        $total = 0;
        $count = 0;
        
        foreach ($ratings as $star => $quantity) {
            $total += $star * $quantity;
            $count += $quantity;
        }
        
        $this->average_rating = $count > 0 ? round($total / $count, 2) : 0;
        $this->total_ratings = $count;
        $this->save();
        
        return $this->average_rating;
    }
}