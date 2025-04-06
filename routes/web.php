<?php

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\Route;

// Serve storage files with and without path
Route::get('/storage/{path?}', function($path = '') {
    $storagePath = storage_path('app/public/' . $path);
    
    // Check if it's a directory
    if (is_dir($storagePath)) {
        // If it's a directory and index.html exists, serve that
        if (file_exists($storagePath . '/index.html')) {
            return response()->file($storagePath . '/index.html');
        }
        
        // For directories without index.html, you could:
        // 1. Show a directory listing (not recommended for production)
        // 2. Redirect to another page
        // 3. Return a custom response
        return response('Storage directory', 200);
    }
    
    // Check if it's a file
    if (file_exists($storagePath)) {
        return response()->file($storagePath);
    }
    
    abort(404);
})->where('path', '.*');

Route::middleware(['web'])->group(function () {
    Route::get('/mail-preview', function() {
        $user = User::first();
        $notification = new CustomVerifyEmail;
        return $notification->toMail($user);
    });
});
