<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Pahri',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '082192598451'
        ])->assignRole('admin');

        User::create([
            'name' => 'Rifal',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '082192598451'
        ])->assignRole('kasir');
    }
}
