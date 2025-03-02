<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function destroy($ulid)
    {
        $user = User::where('ulid', $ulid)->firstOrFail();
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function update(Request $request, $ulid): JsonResponse
    {
        $user = User::where('ulid', $ulid)->firstOrFail();

        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id)
                ],
                'role' => ['sometimes', 'required', 'string', Rule::in(['admin', 'user', 'junkshop_owner', 'baranggay_admin'])],
                'password' => ['sometimes', 'nullable', 'string', 'min:8']
            ]);

            Log::info("📝 User update request", [
                'ulid' => $ulid,
                'id' => $user->id,
                'data' => $validated,
            ]);

            // Extract role for separate handling
            $roleName = $validated['role'] ?? null;
            unset($validated['role']);

            // Handle password if provided
            if (isset($validated['password']) && !empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Update basic user info if any fields are set
            if (!empty($validated)) {
                Log::info("📝 Updating user basic info", [
                    'user_id' => $user->id,
                    'fields' => array_keys($validated)
                ]);

                $user->update($validated);

                Log::info("✅ User basic info updated");
            }

            // Handle role assignment if provided
            if ($roleName) {
                Log::info("📝 Updating user role to '{$roleName}'", [
                    'user_id' => $user->id
                ]);

                try {
                    // Use direct SQL to ensure the role exists
                    $role = DB::table('roles')
                        ->where('name', $roleName)
                        ->where('guard_name', 'web')
                        ->first();

                    if (!$role) {
                        Log::info("📝 Creating new role '{$roleName}'");

                        // Insert the role directly
                        $roleId = DB::table('roles')->insertGetId([
                            'name' => $roleName,
                            'guard_name' => 'web',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Log::info("✅ Created role", ['role_id' => $roleId]);
                    } else {
                        $roleId = $role->id;
                        Log::info("✅ Found existing role", ['role_id' => $roleId]);
                    }

                    // Step 1: Force delete all current roles for this user
                    $deleted = DB::statement("DELETE FROM model_has_roles WHERE model_id = ? AND model_type = ?", [
                        $user->id,
                        User::class
                    ]);

                    Log::info("🧹 Deleted existing roles", ['result' => $deleted]);

                    // Step 2: Insert the new role assignment directly
                    $inserted = DB::statement("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, ?, ?)", [
                        $roleId,
                        User::class,
                        $user->id
                    ]);

                    Log::info("✅ Inserted new role assignment", ['result' => $inserted]);

                    // Step 3: Verify the assignment
                    $verification = DB::select("
                        SELECT r.name FROM model_has_roles mhr
                        JOIN roles r ON mhr.role_id = r.id
                        WHERE mhr.model_id = ?
                        AND mhr.model_type = ?
                    ", [$user->id, User::class]);

                    Log::info("🔍 Role verification", [
                        'found' => !empty($verification),
                        'role' => !empty($verification) ? $verification[0]->name : null
                    ]);

                    // Force permission cache clear
                    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                } catch (\Exception $e) {
                    Log::error("❌ Error in role assignment: " . $e->getMessage(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // Get fresh user data with role for response
            $updatedUser = $user->fresh();

            // Get role directly from database to ensure accuracy
            $roleFromDb = DB::table('model_has_roles')
                ->where('model_id', $updatedUser->id)
                ->where('model_type', User::class)
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name')
                ->first();

            $currentRole = $roleFromDb ? $roleFromDb->name : 'user';

            Log::info("✅ Update completed successfully", [
                'user_id' => $updatedUser->id,
                'current_role' => $currentRole
            ]);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => array_merge(
                    $updatedUser->toArray(),
                    ['role' => $currentRole]
                )
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error updating user: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        // Ensure current user has permission to change roles
        if (!Auth::user()->can('edit roles')) {
            return response()->json([
                'message' => 'Unauthorized to change roles'
            ], 403);
        }

        $validated = $request->validate([
            'role' => 'required|string|in:admin,user,junkshop_owner,baranggay_admin'
        ]);

        // Remove existing roles and assign new role
        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->fresh()
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Store user request received', ['request' => $request->all()]);
        try {
            // Start a database transaction
            return DB::transaction(function () use ($request) {
                // Clear permission cache
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users,email',
                    'role' => 'required|string|in:admin,user,junkshop_owner,baranggay_admin',
                    'password' => 'required|string|min:8',
                ]);

                // Create the user
                $user = User::create([
                    'ulid' => Str::ulid()->toBase32(),
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                ]);

                // Get or create the role
                $role = Role::firstOrCreate([
                    'name' => $validatedData['role'],
                    'guard_name' => 'web'
                ]);

                Log::info("Role found/created for new user", [
                    'role_id' => $role->id,
                    'role_name' => $role->name
                ]);

                // Manually insert the role assignment
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $user->id
                ]);

                Log::info("Role assigned to new user", [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'role_name' => $role->name
                ]);

                // Clear permission cache again
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                return response()->json([
                    'message' => 'User created successfully',
                    'user' => array_merge(
                        $user->toArray(),
                        ['role' => $validatedData['role']]
                    )
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
