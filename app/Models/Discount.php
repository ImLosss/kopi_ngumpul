<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percent',
        'product_id',
        'status'
    ];

    public function product() 
    {
        return $this->belongsTo(Product::class);
    }
}
