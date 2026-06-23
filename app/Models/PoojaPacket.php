<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PoojaPacket extends Model {
    protected $fillable = ['packet_name','image','description','included_flowers','monthly_price','weekly_price','daily_quantity','package_type','status'];
    protected $casts = ['included_flowers' => 'array', 'monthly_price' => 'decimal:2', 'weekly_price' => 'decimal:2'];
    public function subscriptions() { return $this->hasMany(Subscription::class, 'packet_id'); }
}
