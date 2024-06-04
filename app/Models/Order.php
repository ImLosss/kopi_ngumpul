<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_meja',
        'total',
        'status',
        'kasir',
        'status_id',
        'status_pembayaran_id'
    ];

    public function carts() {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }
}
