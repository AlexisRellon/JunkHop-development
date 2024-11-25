<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function update(Request $request, $ulid)
    {
        $user = User::where('ulid', $ulid)->firstOrFail();

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'role' => 'required|string|in:admin,user,junkshop_owner,baranggay_admin',
            ]);

            $user->update($validatedData);

            return response()->json(['message' => 'User updated successfully', 'user' => $user]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
