<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [App\Http\Controllers\AdminController::class, 'login'])->name('admin.login');
    Route::post('/login', [App\Http\Controllers\AdminController::class, 'authenticate'])->name('admin.authenticate');
    Route::post('/logout', [App\Http\Controllers\AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/set-language/{locale}', [App\Http\Controllers\AdminController::class, 'setLanguage'])->name('admin.set_language');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // Taxi Specific Modules
        Route::resource('users', App\Http\Controllers\AdminUserController::class, ['as' => 'admin']);
        Route::get('/drivers-pending', [App\Http\Controllers\AdminUserController::class, 'pendingDrivers'])->name('admin.drivers.pending');
        Route::post('/drivers/{id}/approve', [App\Http\Controllers\AdminUserController::class, 'approveDriver'])->name('admin.drivers.approve');
        Route::post('/users/{id}/wallet', [App\Http\Controllers\AdminUserController::class, 'updateWallet'])->name('admin.users.wallet');
        
        // Ride Management
        Route::get('/rides', [App\Http\Controllers\AdminRideController::class, 'index'])->name('admin.rides.index');
        Route::post('/rides/{id}/price', [App\Http\Controllers\AdminRideController::class, 'updatePrice'])->name('admin.rides.update_price');
        Route::post('/rides/{id}/complete-simple', [App\Http\Controllers\AdminRideController::class, 'completeSimple'])->name('admin.rides.complete_simple');
        Route::post('/rides/{id}/complete-financials', [App\Http\Controllers\AdminRideController::class, 'completeWithFinancials'])->name('admin.rides.complete_financials');
        Route::delete('/rides/{id}', [App\Http\Controllers\AdminRideController::class, 'destroy'])->name('admin.rides.destroy');
        
        // Financial Transactions
        Route::resource('transactions', App\Http\Controllers\AdminTransactionController::class, ['as' => 'admin'])->only(['index']);
        
        Route::resource('coupons', App\Http\Controllers\AdminCouponController::class, ['as' => 'admin']);
        Route::resource('service_areas', App\Http\Controllers\AdminServiceAreaController::class, ['as' => 'admin']);
        
        Route::resource('settings', App\Http\Controllers\AdminSettingController::class, ['as' => 'admin'])->only(['index', 'destroy']);
        Route::post('/settings/logo', [App\Http\Controllers\AdminSettingController::class, 'updateLogo'])->name('admin.settings.logo.update');
        Route::post('/settings/multipliers', [App\Http\Controllers\AdminSettingController::class, 'updateMultipliers'])->name('admin.settings.multipliers.update');
        
        Route::resource('notifications', App\Http\Controllers\AdminNotificationController::class, ['as' => 'admin'])->only(['index', 'destroy']);
        Route::post('/notifications/send', [App\Http\Controllers\AdminNotificationController::class, 'send'])->name('admin.notifications.send');
        Route::post('/notifications/bulk-delete', [App\Http\Controllers\AdminNotificationController::class, 'bulkDelete'])->name('admin.notifications.bulk_delete');
        
        Route::resource('sliders', App\Http\Controllers\AdminSliderController::class, ['as' => 'admin']);
    });
});
