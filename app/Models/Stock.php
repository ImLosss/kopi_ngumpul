<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'jumlah',
        'modal',
        'harga',
    ];

    public function cart()
    {
        return $this->hasMany(Cart::class, 'stock_id', 'id');
    }
}
