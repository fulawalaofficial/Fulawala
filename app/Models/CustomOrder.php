<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomOrder extends Model {
    protected $fillable = ['user_id','address_id','delivery_date','delivery_slot','subtotal','delivery_charge','total_amount','payment_status','order_status'];
    protected $casts = ['delivery_date' => 'date', 'subtotal' => 'decimal:2', 'delivery_charge' => 'decimal:2', 'total_amount' => 'decimal:2'];
    public function user() { return $this->belongsTo(User::class); }
    public function address() { return $this->belongsTo(Address::class); }
    public function items() { return $this->hasMany(CustomOrderItem::class, 'order_id'); }
}
