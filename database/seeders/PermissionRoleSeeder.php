<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'member']);
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'kitchen']);
        Permission::create(['name' => 'adminPanelAccess'])
        ->assignRole('admin');
    }
}
