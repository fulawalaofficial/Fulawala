<?php

/*
|--------------------------------------------------------------------------
| Add this relation inside app/Models/User.php
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Eloquent\Relations\HasMany;

public function addresses(): HasMany
{
    return $this->hasMany(\App\Models\Address::class);
}
