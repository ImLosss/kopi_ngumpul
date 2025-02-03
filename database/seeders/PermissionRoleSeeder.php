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
        Role::create(['name' => 'kasir']);
        Permission::create(['name' => 'userAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'dailyReportAccess'])
        ->assignRole(['admin', 'kasir']);
        Permission::create(['name' => 'roleAccess'])
        ->assignRole('admin');
        Permission::create(['name' => 'permissionAccess'])
        ->assignRole('admin');
        Permission::create(['name' => 'cashierAccess'])
        ->assignRole(['kasir']);
        Permission::create(['name' => 'orderAccess'])
        ->assignRole(['kasir']);
        Permission::create(['name' => 'orderDelete'])
        ->assignRole(['kasir']);
        Permission::create(['name' => 'stockAccess'])
        ->assignRole(['admin', 'kasir']);
        Permission::create(['name' => 'stockUpdate'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'stockDelete'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'stockAdd'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'categoryAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'paymentAccess'])
        ->assignRole(['kasir']);
    }
}
