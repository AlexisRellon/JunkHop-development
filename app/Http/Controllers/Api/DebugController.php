<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function getRoleInfo()
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = Role::all();
        $users = User::all()->map(function($user) {
            return [
                'id' => $user->id,
                'ulid' => $user->ulid,
                'name' => $user->name,
                'email' => $user->email,
                'direct_roles' => DB::table('model_has_roles')
                    ->where('model_id', $user->id)
                    ->where('model_type', User::class)
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('roles.id', 'roles.name', 'roles.guard_name')
                    ->get(),
                'spatie_roles' => $user->roles()->get(['id', 'name', 'guard_name']),
                'role_names' => $user->getRoleNames(),
                'role_attribute' => $user->role
            ];
        });

        return response()->json([
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    public function fixRoles()
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $results = [];

        // Make sure all expected roles exist with web guard
        $roles = ['admin', 'user', 'junkshop_owner', 'baranggay_admin'];
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            $results['roles_created'][] = $role->name;
        }

        // Fix existing role assignments by ensuring they use web guard
        $roleAssignments = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->get();

        foreach ($roleAssignments as $assignment) {
            $oldRole = Role::find($assignment->role_id);
            if ($oldRole && $oldRole->guard_name !== 'web') {
                // Find or create equivalent web guard role
                $webRole = Role::firstOrCreate([
                    'name' => $oldRole->name,
                    'guard_name' => 'web'
                ]);

                // Update assignment to use web guard role
                DB::table('model_has_roles')
                    ->where('role_id', $assignment->role_id)
                    ->where('model_id', $assignment->model_id)
                    ->where('model_type', $assignment->model_type)
                    ->update(['role_id' => $webRole->id]);

                $results['fixed_assignments'][] = [
                    'model_id' => $assignment->model_id,
                    'old_role' => $oldRole->name . ' (guard: ' . $oldRole->guard_name . ')',
                    'new_role' => $webRole->name . ' (guard: web)'
                ];
            }
        }

        // Clear permission cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'message' => 'Role fixes applied',
            'results' => $results
        ]);
    }
}
