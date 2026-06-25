<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerProduct extends Model
{
    protected $fillable = [
        'flower_name',
        'image',
        'category',
        'price',
        'unit',
        'stock_status',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}