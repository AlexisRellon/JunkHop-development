<?php

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\Route;

// Serve storage files
Route::get('/storage/{path}', function($path) {
    $storagePath = storage_path('app/public/' . $path);
    if (!file_exists($storagePath)) {
        abort(404);
    }
    return response()->file($storagePath);
})->where('path', '.*');

Route::middleware(['web'])->group(function () {
    Route::get('/mail-preview', function() {
        $user = User::first();
        $notification = new CustomVerifyEmail;
        return $notification->toMail($user);
    });
});
