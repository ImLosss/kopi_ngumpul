<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'no_meja'
    ];

    public function order()
    {
        return $this->hasMany(Order::class, 'no_meja', 'id');
    }
}
