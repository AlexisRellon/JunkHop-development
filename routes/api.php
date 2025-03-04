<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\JunkshopController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardStatisticsController;
use App\Models\DashboardStatistic;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return ['ok' => true, 'message' => 'Welcome to the API'];
});

Route::prefix('api/v1')->group(function () {
    Route::get('login/{provider}/redirect', [AuthController::class, 'redirect'])->name('login.provider.redirect');
    Route::get('login/{provider}/callback', [AuthController::class, 'callback'])->name('login.provider.callback');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('forgot-password', [AuthController::class, 'sendResetPasswordLink'])->middleware('throttle:5,1')->name('password.email');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
    Route::post('verification-notification', [AuthController::class, 'verificationNotification'])->middleware('throttle:verification-notification')->name('verification.send');
    Route::get('verify-email/{ulid}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    // Mailer Preview
    Route::get('/mail-preview', function () {
        $user = App\Models\User::first();
        $notification = new App\Notifications\CustomVerifyEmail;
        return $notification->toMail($user);
    });

    // Dashboard endpoint
    Route::get('/dashboard-statistics', [DashboardController::class, 'getStatistics']);

    // Route to fetch all junkshops
    Route::get('junkshop', [JunkshopController::class, 'index'])->name('junkshops.index');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('devices/disconnect', [AuthController::class, 'deviceDisconnect'])->name('devices.disconnect');
        Route::get('devices', [AuthController::class, 'devices'])->name('devices');
        Route::get('user', [AuthController::class, 'user'])->name('user');

        Route::post('account/update', [AccountController::class, 'update'])->name('account.update');
        Route::post('account/password', [AccountController::class, 'password'])->name('account.password');

        Route::middleware(['throttle:uploads'])->group(function () {
            Route::post('upload', [UploadController::class, 'image'])->name('upload.image');
        });

        // Junkshop routes
        Route::get('junkshop/{ulid}', [JunkshopController::class, 'show'])->name('junkshop.show');
        Route::put('junkshop/{ulid}', [JunkshopController::class, 'update'])->name('junkshop.update');
        Route::post('junkshop', [JunkshopController::class, 'store'])->name('junkshop.store');
        Route::delete('junkshop/{ulid}', [JunkshopController::class, 'destroy'])->name('junkshop.destroy');

        // Item routes
        Route::get('junkshop/{ulid}/items', [ItemController::class, 'index'])->name('items.index');
        Route::post('junkshop/{ulid}/items', [ItemController::class, 'store'])->name('items.store');
        Route::put('junkshop/{ulid}/items/{itemId}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('junkshop/{ulid}/items/{itemId}', [ItemController::class, 'destroy'])->name('items.destroy');

        Route::apiResource('junkshops', JunkshopController::class);

        // User routes - consolidated and cleaned up
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{ulid}', [UserController::class, 'update']);
        Route::delete('/users/{ulid}', [UserController::class, 'destroy']);
        Route::put('/users/{user}/update-role', [UserController::class, 'updateRole'])->middleware('can:edit roles');

        // Debug routes
        Route::prefix('debug')->group(function() {
            Route::get('/roles', [\App\Http\Controllers\Api\DebugController::class, 'getRoleInfo']);
            Route::post('/fix-roles', [\App\Http\Controllers\Api\DebugController::class, 'fixRoles']);
        });
    });
});
