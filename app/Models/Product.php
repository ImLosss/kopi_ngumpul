<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'jumlah',
        'modal',
        'harga',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function discount()
    {
        return $this->hasMany(Discount::class, 'product_id', 'id');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class, 'product_id', 'id');
    }

    public function partnerProduct()
    {
        return $this->hasOne(PartnerProduct::class, 'product_id', 'id');
    }
}
