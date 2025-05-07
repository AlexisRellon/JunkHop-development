<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use App\Rules\TemporaryFileExists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(Request $request): JsonResponse
    {
        $request->merge([
            // Remove extra spaces and non-word characters
            'name' => $request->name ? Str::squish(Str::onlyWords($request->name)) : '',
        ]);

        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'avatar' => ['nullable', 'string', new TemporaryFileExists],
        ]);

        // Handle avatar update
        if ($request->has('avatar') && $request->avatar !== $user->avatar) {
            // Delete old avatar if it exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Update user avatar with the new path
            $user->avatar = $request->avatar;
            
            // Delete temporary upload record since it's now permanent
            TemporaryUpload::where('path', $request->avatar)->delete();
        }

        $email = $user->email;
        
        // Track changes for activity log
        $changes = [];
        if ($user->name !== $request->name) {
            $changes['name'] = [
                'old' => $user->name,
                'new' => $request->name
            ];
        }
        if ($user->email !== $request->email) {
            $changes['email'] = [
                'old' => $user->email,
                'new' => $request->email
            ];
        }
        if ($request->has('avatar') && $request->avatar !== $user->avatar) {
            $changes['avatar'] = [
                'old' => $user->avatar ?? 'none',
                'new' => $request->avatar
            ];
        }
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Log profile updates with detailed changes
        if (!empty($changes)) {
            \App\Services\ActivityLogger::log(
                'user',
                $user,
                'updated',
                null,
                $user->ulid,
                $changes
            );
        }

        if ($email !== $request->email) {
            $user->email_verified_at = null;
            $user->save(['timestamps' => false]);
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Update the user's password.
     * @throws ValidationException
     */
    public function password(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'min:8', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ]);

        $user = $request->user();
        abort_if(!$user->password, 403, __('Access denied.'));

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('auth.password'),
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log password change (without exposing the actual password)
        \App\Services\ActivityLogger::log(
            'user',
            $user,
            'updated',
            "User {$user->name} changed their password",
            $user->ulid
        );

        return response()->json([
            'ok' => true,
        ]);
    }
}
