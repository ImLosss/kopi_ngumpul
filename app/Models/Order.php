<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public function carts() {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id', 'id');
    }
}
