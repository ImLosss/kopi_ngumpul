<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['status'];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function ingredient_recipe()
    {
        return $this->hasMany(IngredientRecipe::class, 'product_id', 'id');
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'ingredient_recipe')
                    ->withPivot('gram_ml')
                    ->withTimestamps();
    }

    // Accessor untuk attribute status
    public function getStatusAttribute()
    {
        // Misal, default status adalah true
        $status = true;
        // Cek setiap stock, jika kondisi tertentu terpenuhi, ubah status
        foreach ($this->stocks as $stock) {
            if ($stock->pivot->gram_ml > $stock->jumlah_gr) {
                $status = false;
                break;
            }
        }
        return $status;
    }
}
