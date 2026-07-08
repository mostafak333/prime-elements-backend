<?php

use App\Http\Controllers\AdminApi\Auth\AdminAuthController;
use App\Http\Controllers\AdminApi\Category\CategoryController;
use App\Http\Controllers\AdminApi\DeliveryMethod\DeliveryMethodController;
use App\Http\Controllers\AdminApi\PaymentMethod\PaymentMethodController;
use App\Http\Controllers\AdminApi\Product\ProductController;
use App\Http\Controllers\AdminApi\Settings\SettingsController;
use App\Http\Controllers\AdminApi\Title\TitleController;
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
    Route::post('/admins', [AdminAuthController::class, 'createAdmin']);
    Route::post('/change-password', [AdminAuthController::class, 'changePassword']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);

    Route::apiResource('titles', TitleController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('delivery-methods', DeliveryMethodController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
});
