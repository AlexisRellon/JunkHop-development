<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixUserRoles extends Command
{
    protected $signature = 'fix:roles {default=user : Default role to assign to users without roles}';
    protected $description = 'Fix user role assignments in the database';

    public function handle()
    {
        $this->info('=== Fixing User Role Assignments ===');

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get default role
        $defaultRoleName = $this->argument('default');

        // Ensure the default role exists
        $defaultRole = Role::firstOrCreate([
            'name' => $defaultRoleName,
            'guard_name' => 'web'
        ]);

        $this->info("Using default role: {$defaultRoleName}");

        // Fix all users
        $users = User::all();
        $this->info("Found {$users->count()} users to check");

        $fixed = 0;

        foreach ($users as $user) {
            $this->info("Processing user: {$user->name} (ID: {$user->id}, ULID: {$user->ulid})");

            // Get current roles
            $currentRoles = $user->getRoleNames();
            $this->info("Current roles: " . ($currentRoles->isEmpty() ? 'none' : $currentRoles->implode(', ')));

            // If user has no roles, add default role
            if ($currentRoles->isEmpty()) {
                // First try with Spatie's methods
                $this->info("Assigning default role '{$defaultRoleName}' with Spatie methods");
                try {
                    $user->assignRole($defaultRole);
                } catch (\Exception $e) {
                    $this->error("Error with Spatie: " . $e->getMessage());

                    // Try direct DB insertion as backup
                    $this->info("Trying direct database insertion");
                    DB::table('model_has_roles')->insert([
                        'role_id' => $defaultRole->id,
                        'model_type' => get_class($user),
                        'model_id' => $user->id
                    ]);
                }

                // Verify assignment
                $checkRole = DB::table('model_has_roles')
                    ->where('model_id', $user->id)
                    ->where('model_type', get_class($user))
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('roles.name')
                    ->first();

                if ($checkRole) {
                    $this->info("Successfully assigned role: {$checkRole->name}");
                    $fixed++;
                } else {
                    $this->error("Failed to assign role");
                }
            } else {
                $this->info("User already has roles - no fix needed");
            }

            $this->line('------------------------------');
        }

        $this->info("Fixed {$fixed} users with role assignments");

        // Clear permission cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return 0;
    }
}
