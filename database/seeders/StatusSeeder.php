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
            'desc' => 'Menunggu konfirmasi Dapur',
        ]);

        Status::create([
            'desc' => 'Sedang dibuat',
        ]);

        Status::create([
            'desc' => 'Pengantaran',
        ]);

        Status::create([
            'desc' => 'Selesai',
        ]);
    }
}
