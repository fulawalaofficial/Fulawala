<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
class Staff extends Model {
    protected $fillable = ['name','mobile','email','password','role','status'];
    protected $hidden = ['password'];
    public function setPasswordAttribute($value) { $this->attributes['password'] = Hash::make($value); }
}
