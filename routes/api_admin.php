<?php

use App\Http\Controllers\AdminApi\Admin\AdminRoleController;
use App\Http\Controllers\AdminApi\Admin\AdminController;
use App\Http\Controllers\AdminApi\Auth\AdminAuthController;
use App\Http\Controllers\AdminApi\Category\CategoryController;
use App\Http\Controllers\AdminApi\DeliveryMethod\DeliveryMethodController;
use App\Http\Controllers\AdminApi\Order\AdminOrderController;
use App\Http\Controllers\AdminApi\PaymentMethod\PaymentMethodController;
use App\Http\Controllers\AdminApi\Permission\PermissionController;
use App\Http\Controllers\AdminApi\Product\ProductController;
use App\Http\Controllers\AdminApi\Role\RoleController;
use App\Http\Controllers\AdminApi\Settings\SettingsController;
use App\Http\Controllers\AdminApi\Review\ReviewController;
use App\Http\Controllers\AdminApi\LandingBanner\LandingBannerController;
use App\Http\Controllers\AdminApi\Title\TitleController;
use App\Http\Controllers\AdminApi\User\UserController;
use Illuminate\Support\Facades\Route;


// =========================================================================
// PUBLIC ADMIN AUTH
// =========================================================================
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/register-invitation/set-password', [AdminAuthController::class, 'setPassword']);
Route::post('/forgot-password', [AdminAuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AdminAuthController::class, 'resetPassword']);

// =========================================================================
// PROTECTED ADMIN ENDPOINTS (Guard: api-admin)
// =========================================================================
Route::group(['middleware' => ['auth:api-admin']], function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::post('/change-password', [AdminAuthController::class, 'changePassword']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);

    Route::apiResource('titles', TitleController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('delivery-methods', DeliveryMethodController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);

    // =========================================================================
    // ROLES & PERMISSIONS
    // =========================================================================
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // =========================================================================
    // ADMIN ROLE ASSIGNMENT
    // =========================================================================
    Route::get('/admins/{admin}/roles', [AdminRoleController::class, 'show'])->name('admins.roles.show');
    Route::put('/admins/{admin}/roles', [AdminRoleController::class, 'sync'])->name('admins.roles.sync');
    Route::delete('/admins/{admin}/roles/{role}', [AdminRoleController::class, 'destroy'])->name('admins.roles.destroy');

    // =========================================================================
    // LANDING BANNERS
    // =========================================================================

    Route::get('/landing-banners', [LandingBannerController::class, 'index'])->name('landing-banners.index');
    Route::get('/landing-banners/{landing_banner}', [LandingBannerController::class, 'show'])->name('landing-banners.show');
    Route::post('/landing-banners', [LandingBannerController::class, 'store'])
        ->name('landing-banners.store');
    Route::put('/landing-banners/{landing_banner}', [LandingBannerController::class, 'update'])
        ->name('landing-banners.update');
    Route::delete('/landing-banners/{landing_banner}', [LandingBannerController::class, 'destroy'])
        ->name('landing-banners.destroy');

    // =========================================================================
    // USER MANAGEMENT
    // =========================================================================
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');

    // =========================================================================
    // Admin MANAGEMENT
    // =========================================================================
    Route::post('/admins', [AdminController::class, 'createAdmin'])->name('admins.create');
    Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
    Route::put('/admins/{id}/status', [AdminController::class, 'updateStatus'])->name('admins.update-status');
    Route::put('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');

    // =========================================================================
    // REVIEW MANAGEMENT
    // =========================================================================
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::put('/reviews/{review}/status', [ReviewController::class, 'updateStatus'])->name('reviews.update-status');

    // =========================================================================
    // ORDER MANAGEMENT
    // =========================================================================
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
});
