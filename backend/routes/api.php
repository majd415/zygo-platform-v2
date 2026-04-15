<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\DriverDocumentController;
use App\Http\Controllers\Api\DriverStatsController;
use App\Http\Controllers\Api\ServiceAreaController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\WalletTransactionController;
use App\Http\Controllers\Api\AdvertisementController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'taxi_backend'
    ]);
});

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Taxi API Root'
    ]);
});

Route::middleware('throttle:60,1')->prefix('auth')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendCode']);
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('/settings', [SettingController::class, 'index']);
Route::get('/advertisements', [AdvertisementController::class, 'index']);
Route::get('/rides/heatmap', [RideController::class, 'getHeatmapData']);
Route::get('/ride/track/{token}', [RideController::class, 'trackByToken']);

Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/upload-photo', [AuthController::class, 'uploadProfilePhoto']);
    Route::post('/user/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Driver Onboarding
    Route::post('/driver/register-step', [DriverDocumentController::class, 'submitStep']);
    Route::post('/driver/register-documents', [DriverDocumentController::class, 'submitStep']);
    Route::get('/driver/status', [DriverDocumentController::class, 'getStatus']);
    Route::post('/driver/toggle-online', [AuthController::class, 'toggleOnline']);
    Route::post('/driver/update-location', [AuthController::class, 'updateLocation']);
    Route::get('/driver/stats', [DriverStatsController::class, 'getStats']);


    // Service Areas
    Route::get('/service-areas', [ServiceAreaController::class, 'index']);

    // Rides
    Route::post('/rides/request', [RideController::class, 'requestRide']);
    Route::post('/rides/schedule', [RideController::class, 'scheduleRide']);
    Route::post('/rides/{id}/accept', [RideController::class, 'acceptRide']);
    Route::post('/rides/{id}/status', [RideController::class, 'updateStatus']);
    Route::post('/rides/{id}/update-location', [RideController::class, 'updateLocation']);
    Route::post('/rides/{id}/rate', [RideController::class, 'rateDriver']);
    Route::get('/rides/active', [RideController::class, 'getActiveRide']);
    Route::get('/rides/{id}/status', [RideController::class, 'getRideStatus']);
    Route::get('/rides/history', [RideController::class, 'history']);

    // Saved Locations
    Route::get('/user/saved-locations', [AuthController::class, 'getSavedLocations']);
    Route::post('/user/saved-locations', [AuthController::class, 'saveLocation']);


    // Wallets
    // Maps Proxy
    Route::get('/maps/autocomplete', [App\Http\Controllers\Api\MapsController::class, 'autocomplete']);
    Route::get('/maps/place-details', [App\Http\Controllers\Api\MapsController::class, 'placeDetails']);
    Route::get('/maps/directions', [App\Http\Controllers\Api\MapsController::class, 'directions']);
    Route::get('/maps/reverse-geocode', [App\Http\Controllers\Api\MapsController::class, 'reverseGeocode']);
    Route::get('/wallet/balance', [WalletTransactionController::class, 'getBalance']);
    Route::get('/wallet/transactions', [WalletTransactionController::class, 'getTransactions']);
    Route::post('/wallet/topup', [WalletTransactionController::class, 'topup']);
    Route::post('/wallet/recharge', [WalletTransactionController::class, 'recharge']);
    Route::post('/wallet/gift/verify', [WalletTransactionController::class, 'verifyGiftRecipient']);
    Route::post('/wallet/gift/send', [WalletTransactionController::class, 'sendGift']);

    // Coupons
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

    // Broadcasting Authorization
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });

    Broadcast::routes();
});


