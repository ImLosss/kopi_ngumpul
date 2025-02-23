<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['status', 'stock'];
    
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

    // Accessor untuk attribute status
    public function getStockAttribute()
    {
        // Inisialisasi dengan null agar kita bisa membandingkan nilai pertama yang valid
        $minStock = null;

        foreach ($this->stocks as $item) {
            if ($item->pivot->gram_ml < $item->jumlah_gr) {
                $stockCek = floor($item->jumlah_gr / $item->pivot->gram_ml);
                // Jika belum ada nilai atau nilai saat ini lebih kecil daripada yang sebelumnya
                if ($minStock === null || $stockCek < $minStock) {
                    $minStock = $stockCek;
                }
            }
        }

        // Jika tidak ada stock yang memenuhi kondisi, kembalikan 0
        return $minStock !== null ? $minStock : 0;
    }
}
