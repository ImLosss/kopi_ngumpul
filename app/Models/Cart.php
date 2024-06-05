<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_id',
        'diskon_id',
        'jumlah',
        'total_diskon',
        'total',
        'status_id',
        'pembayaran'
    ];

    public function cart() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function discount() {
        return $this->belongsTo(Discount::class, 'diskon_id', 'id');
    }
}
