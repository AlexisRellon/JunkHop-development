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
            // Check if user has admin role before allowing role updates
            if (isset($request->role) && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'message' => 'Only administrators can update user roles'
                ], 403);
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id)
                ],
                'role' => ['sometimes', Rule::in(['admin', 'user', 'junkshop_owner', 'baranggay_admin'])]
            ]);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (isset($validated['role']) && Auth::user()->hasRole('admin')) {
                $role = Role::findByName($validated['role']);
                $user->syncRoles([$role]);
            }

            $user->refresh();
            
            return response()->json([
                'message' => 'User updated successfully',
                'user' => array_merge(
                    $user->toArray(),
                    ['role' => $user->getRoleNames()->first() ?? 'user']
                )
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating user',
                'errors' => method_exists($e, 'errors') ? $e->errors() : [$e->getMessage()]
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
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'role' => 'required|string|in:admin,user,junkshop_owner,baranggay_admin',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'ulid' => Str::ulid()->toBase32(),
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
                'password' => bcrypt($validatedData['password']),
            ]);

            return response()->json(['message' => 'User created successfully', 'user' => $user]);
        } catch (ValidationException $e) {
            Log::warning('Validation error while creating user', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
