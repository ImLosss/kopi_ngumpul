<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create([
            'desc' => 'Cart',
        ]);

        Status::create([
            'desc' => 'Dapur',
        ]);

        Status::create([
            'desc' => 'Siap diantarkan',
        ]);

        Status::create([
            'desc' => 'Selesai',
        ]);
    }
}
