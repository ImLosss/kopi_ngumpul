<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
    }

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
    }
}
