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
        Role::create(['name' => 'partner']);
        Role::create(['name' => 'dapur']);
        Role::create(['name' => 'kasir']);
        Role::create(['name' => 'pelayan']);
        Permission::create(['name' => 'userAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'dailyReportAccess'])
        ->assignRole(['admin', 'partner', 'kasir']);
        Permission::create(['name' => 'roleAccess'])
        ->assignRole('admin');
        Permission::create(['name' => 'permissionAccess'])
        ->assignRole('admin');
        Permission::create(['name' => 'cashierAccess'])
        ->assignRole(['kasir', 'pelayan']);
        Permission::create(['name' => 'cashierPartnerAccess'])
        ->assignRole(['partner']);
        Permission::create(['name' => 'orderAccess'])
        ->assignRole(['kasir', 'dapur', 'partner', 'pelayan']);
        Permission::create(['name' => 'orderDelete'])
        ->assignRole(['dapur']);
        Permission::create(['name' => 'productAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'catogoryAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'discountAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'paymentAccess'])
        ->assignRole(['admin', 'kasir', 'partner']);
        Permission::create(['name' => 'updateStatusTwo'])
        ->assignRole(['dapur', 'admin']);
        Permission::create(['name' => 'updateStatusThree'])
        ->assignRole(['dapur', 'admin']);
        Permission::create(['name' => 'updateStatusFourth'])
        ->assignRole(['admin', 'pelayan', 'partner', 'kasir']);
        Permission::create(['name' => 'deleteStatusTwo'])
        ->assignRole(['dapur', 'admin', 'kasir', 'partner']);
        Permission::create(['name' => 'deleteStatusThree'])
        ->assignRole(['dapur', 'admin']);
        Permission::create(['name' => 'deleteStatusFourth'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'partnerProductAcceess'])
        ->assignRole(['partner']);
        Permission::create(['name' => 'categoryAccess'])
        ->assignRole(['admin']);
        Permission::create(['name' => 'tableAccess'])
        ->assignRole(['admin', 'kasir', 'partner']);
        Permission::create(['name' => 'updateTable'])
        ->assignRole(['admin']);
    }
}
