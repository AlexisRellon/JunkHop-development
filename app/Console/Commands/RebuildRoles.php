<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RebuildRoles extends Command
{
    protected $signature = 'rebuild:roles {--force : Force rebuild even if it will delete existing data}';
    protected $description = 'Completely rebuild the roles and permissions tables';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL existing roles and permissions. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('=== Rebuilding Roles and Permissions ===');

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Backup current role assignments
        $roleAssignments = [];
        $users = User::all();
        foreach ($users as $user) {
            $roleName = $user->getRoleNames()->first();
            if ($roleName) {
                $roleAssignments[$user->id] = $roleName;
            }
        }

        $this->info("Backed up " . count($roleAssignments) . " role assignments");

        // Truncate tables
        Schema::disableForeignKeyConstraints();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->info("Tables truncated");

        // Create default roles
        $roles = ['admin', 'user', 'junkshop_owner', 'baranggay_admin'];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }
        $this->info("Created default roles");

        // Create permissions
        $permissions = ['edit roles', 'manage users', 'manage junkshops'];
        foreach ($permissions as $permName) {
            Permission::create(['name' => $permName, 'guard_name' => 'web']);
        }
        $this->info("Created default permissions");

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->givePermissionTo(Permission::all());
        $this->info("Assigned all permissions to admin role");

        // Restore role assignments
        $restored = 0;
        foreach ($roleAssignments as $userId => $roleName) {
            $user = User::find($userId);
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if ($user && $role) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_id' => $user->id,
                    'model_type' => User::class
                ]);
                $restored++;
            }
        }

        $this->info("Restored $restored role assignments");

        // Clear cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Role system rebuilt successfully");
        return 0;
    }
}
