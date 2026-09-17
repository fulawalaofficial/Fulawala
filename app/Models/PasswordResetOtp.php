<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'otp_hash',
        'attempts',
        'expires_at',
    ];

    protected $hidden = [
        'otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
        ];
    }
}
