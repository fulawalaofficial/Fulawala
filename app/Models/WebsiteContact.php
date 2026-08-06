<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteContact extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'service', 'message', 'status', 'ip_address', 'user_agent'];
}
