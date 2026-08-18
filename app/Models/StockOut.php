<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    // Relasi ke rincian batch
    public function details()
    {
        return $this->hasMany(StockOutDetail::class, 'stock_out_id');
    }
}
