<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'address_type',
        'name',
        'number',
        'address',
        'city',
        'state',
        'pincode',
        'landmark',
        'is_default',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}