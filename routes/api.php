<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\JunkshopController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardStatisticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\WantedMaterialController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\MaterialBidController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\QualityVerificationController;
use App\Models\DashboardStatistic;
use Illuminate\Support\Facades\Route;

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

        // Merchant routes
        Route::get('/merchants', [MerchantController::class, 'index']);
        Route::post('/merchants', [MerchantController::class, 'store']);
        Route::get('/merchants/{ulid}', [MerchantController::class, 'show']);
        Route::put('/merchants/{ulid}', [MerchantController::class, 'update']);
        Route::delete('/merchants/{ulid}', [MerchantController::class, 'destroy']);
        Route::get('/merchant/profile', [MerchantController::class, 'profile']);
        
        // Simplified merchant routes
        Route::prefix('merchant')->group(function() {
            Route::get('/profile', [MerchantController::class, 'show']);
            Route::post('/profile', [MerchantController::class, 'store']);
            Route::put('/profile', [MerchantController::class, 'update']);
        });
        
        // Wanted Material Marketplace routes
        Route::prefix('marketplace')->group(function() {
            Route::get('/wanted-materials', [WantedMaterialController::class, 'index']);
            Route::get('/wanted-materials/{ulid}', [WantedMaterialController::class, 'show']);
            Route::post('/wanted-materials', [WantedMaterialController::class, 'store']);
            Route::put('/wanted-materials/{ulid}', [WantedMaterialController::class, 'update']);
            Route::delete('/wanted-materials/{ulid}', [WantedMaterialController::class, 'destroy']);
            Route::get('/my-listings', [WantedMaterialController::class, 'myListings']);
            Route::post('/wanted-materials/{ulid}/toggle-active', [WantedMaterialController::class, 'toggleActive']);
        });
        
        // Bidding System routes
        Route::prefix('bids')->group(function() {
            // Legacy bid routes if needed
            Route::get('/my-bids', [BidController::class, 'myBids']);
            Route::get('/received-bids', [BidController::class, 'receivedBids']);
            Route::post('/', [BidController::class, 'store']);
            Route::get('/{ulid}', [BidController::class, 'show']);
            Route::put('/{ulid}/status', [BidController::class, 'updateStatus']);
            Route::post('/{ulid}/complete', [BidController::class, 'markCompleted']);
            Route::post('/{ulid}/cancel', [BidController::class, 'cancel']);
            Route::get('/{ulid}/counter-offers', [BidController::class, 'getCounterOffers']);
        });
        
        // Material Marketplace Bidding System routes
        Route::prefix('material-bids')->group(function() {
            Route::get('/wanted-material/{wantedMaterialId}', [MaterialBidController::class, 'index']);
            Route::post('/wanted-material/{wantedMaterialId}', [MaterialBidController::class, 'store']);
            Route::get('/my-bids', [MaterialBidController::class, 'myBids']);
            Route::get('/received-bids', [MaterialBidController::class, 'receivedBids']);
            Route::put('/{bidId}/status', [MaterialBidController::class, 'updateStatus']);
            Route::delete('/{bidId}', [MaterialBidController::class, 'cancel']);
        });

        // Inventory Visibility System routes
        Route::prefix('inventory')->group(function() {
            Route::get('/junkshops/{junkshopUlid}/items', [InventoryController::class, 'index']);
            Route::get('/junkshops/{junkshopUlid}/items/{itemId}', [InventoryController::class, 'show']);
            Route::put('/junkshops/{junkshopUlid}/items/{itemId}', [InventoryController::class, 'update']);
            Route::get('/junkshops/{junkshopUlid}/history', [InventoryController::class, 'history']);
            Route::get('/notifications/preferences', [InventoryController::class, 'getNotificationPreferences']);
            Route::put('/notifications/preferences', [InventoryController::class, 'updateNotificationPreferences']);
            Route::post('/notifications/interested-items', [InventoryController::class, 'addInterestedItem']);
            Route::delete('/notifications/interested-items', [InventoryController::class, 'removeInterestedItem']);
        });
        
        // Auto-Renewal System routes
        Route::prefix('subscriptions')->group(function() {
            Route::get('/', [SubscriptionController::class, 'index']);
            Route::post('/', [SubscriptionController::class, 'store']);
            Route::get('/{ulid}', [SubscriptionController::class, 'show']);
            Route::put('/{ulid}', [SubscriptionController::class, 'update']);
            Route::post('/{ulid}/cancel', [SubscriptionController::class, 'cancel']);
            Route::post('/{ulid}/renew-now', [SubscriptionController::class, 'renewNow']);
            Route::get('/{ulid}/history', [SubscriptionController::class, 'getRenewalHistory']);
            Route::get('/preferences', [SubscriptionController::class, 'getPreferences']);
            Route::put('/preferences', [SubscriptionController::class, 'updatePreferences']);
        });
        
        // Quality Verification System routes
        Route::prefix('verifications')->group(function() {
            // Merchant routes
            Route::get('/', [QualityVerificationController::class, 'index']);
            Route::post('/', [QualityVerificationController::class, 'store']);
            Route::get('/{ulid}', [QualityVerificationController::class, 'show']);
            
            // Junkshop routes
            Route::get('/junkshop', [QualityVerificationController::class, 'junkshopVerifications']);
            Route::put('/{ulid}/status', [QualityVerificationController::class, 'updateStatus']);
            
            // Quality standards and methods
            Route::get('/items/{itemId}/standards', [QualityVerificationController::class, 'getQualityStandards']);
            Route::get('/items/{itemId}/methods', [QualityVerificationController::class, 'getVerificationMethods']);
        });

        // Debug routes
        Route::prefix('debug')->group(function() {
            Route::get('/roles', [\App\Http\Controllers\Api\DebugController::class, 'getRoleInfo']);
            Route::post('/fix-roles', [\App\Http\Controllers\Api\DebugController::class, 'fixRoles']);
        });
    });
});
