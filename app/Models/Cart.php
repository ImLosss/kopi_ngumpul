<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu',
        'product_id',
        'order_id',
        'diskon_id',
        'jumlah',
        'harga',
        'total_diskon',
        'total',
        'profit',
        'status_id',
        'pembayaran',
        'note'
    ];

    public function order() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function discount() {
        return $this->belongsTo(Discount::class, 'diskon_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }
}
