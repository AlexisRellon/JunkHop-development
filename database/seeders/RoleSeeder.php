<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles with web guard (important for Sanctum compatibility)
        $roles = ['admin', 'user', 'junkshop_owner', 'baranggay_admin'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'  // Important: Use web guard, not sanctum
            ]);
        }

        // Create some basic permissions
        $permissions = [
            'edit roles',
            'manage users',
            'manage junkshops',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::where('guard_name', 'web')->get());
        }
    }
}
