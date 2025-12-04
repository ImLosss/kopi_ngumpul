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
        Role::create(['name' => 'staff']);
        Permission::create(['name' => 'dashboardAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'userAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'roleAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'stockAccess'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'stockCreate'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'stockUpdate'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'stockDelete'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'categoryAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'salesRecordAccess'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'salesRecordCreate'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'salesRecordUpdate'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'salesRecordDelete'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'stockInAccess'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'stockInCreate'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'stockInUpdate'])
        ->assignRole(['admin', 'staff']);
        Permission::create(['name' => 'stockInDelete'])
        ->assignRole(['admin', 'staff']);
    }
}
