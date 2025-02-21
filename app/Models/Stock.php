<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'jumlah_gr'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'ingredient_recipe')
                    ->withPivot('gram_ml')
                    ->withTimestamps();
    }
}
