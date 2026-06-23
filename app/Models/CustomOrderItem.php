<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomOrderItem extends Model {
    protected $fillable = ['order_id','flower_product_id','quantity','unit','price','total'];
    protected $casts = ['price' => 'decimal:2', 'total' => 'decimal:2'];
    public function order() { return $this->belongsTo(CustomOrder::class, 'order_id'); }
    public function flower() { return $this->belongsTo(FlowerProduct::class, 'flower_product_id'); }
}
