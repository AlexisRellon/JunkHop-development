<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DebugRoles extends Command
{
    protected $signature = 'debug:roles {ulid? : The ULID of a specific user}';
    protected $description = 'Debug role assignments for users';

    public function handle()
    {
        $this->info('=== Debugging Role Assignments ===');

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Show available roles
        $this->info('Available Roles:');
        $roles = Role::all();
        $this->table(
            ['ID', 'Name', 'Guard Name'],
            $roles->map(fn($role) => [$role->id, $role->name, $role->guard_name])->toArray()
        );

        // Show role assignments
        $userUlid = $this->argument('ulid');
        $query = User::query();

        if ($userUlid) {
            $query->where('ulid', $userUlid);
        }

        $users = $query->get();

        $this->info('User Role Assignments:');
        $tableData = [];

        foreach ($users as $user) {
            $directRoles = DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', User::class)
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.id', 'roles.name', 'roles.guard_name')
                ->get();

            $spatieRoles = $user->roles()->get();

            $tableData[] = [
                $user->id,
                $user->ulid,
                $user->name,
                $user->email,
                $directRoles->pluck('name')->implode(', '),
                $spatieRoles->pluck('name')->implode(', '),
                $user->getRoleNames()->implode(', ')
            ];
        }

        $this->table(
            ['ID', 'ULID', 'Name', 'Email', 'DB Roles', 'Spatie Roles', 'getRoleNames()'],
            $tableData
        );

        return 0;
    }
}
