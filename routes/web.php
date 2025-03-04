<?php

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/mail-preview', function() {
        $user = User::first();
        $notification = new CustomVerifyEmail;
        return $notification->toMail($user);
    });
});
