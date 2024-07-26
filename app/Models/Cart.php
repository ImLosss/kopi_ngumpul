<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

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
        'partner_price',
        'partner_total',
        'partner_profit',
        'status_id',
        'pembayaran',
        'payment_method',
        'note',
        'update_status_by',
        'update_payment_by'
    ];

    protected $dates = ['deleted_at'];

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
