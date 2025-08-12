<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'download products']);
        Permission::create(['name' => 'purchase products']);

        // Create roles and assign permissions
        $member = Role::create(['name' => 'member']);
        $member->givePermissionTo([
            'download products',
            'purchase products'
        ]);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view dashboard',
            'manage products',
            'manage users',
            'view orders',
            'download products',
            'purchase products'
        ]);

        $this->command->info('Roles e Permissions criados com sucesso!');
    }
}
