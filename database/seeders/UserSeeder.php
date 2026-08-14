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
            'name' => 'Awaluddin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '082192598451'
        ])->assignRole('admin');

        User::create([
            'name' => 'Irian',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '082192598451'
        ])->assignRole('staff');

        User::create([
            'name' => 'Owner',
            'email' => 'pimpinan@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '082192598451'
        ])->assignRole('pimpinan');
    }
}
