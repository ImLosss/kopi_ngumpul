<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Table::create([
            'no_meja' => 1,
        ]);
        
        Table::create([
            'no_meja' => 2,
            'status' => 'terpakai'
        ]);

        Table::create([
            'no_meja' => 3,
        ]);
    }
}
