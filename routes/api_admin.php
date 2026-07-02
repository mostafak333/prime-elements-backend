<?php

use App\Http\Controllers\AdminApi\Auth\AdminAuthController;
use App\Http\Controllers\AdminApi\Category\CategoryController;
use App\Http\Controllers\AdminApi\Product\ProductController;
use App\Http\Controllers\AdminApi\Title\TitleController;
use App\Http\Controllers\AdminApi\DeliveryMethod\DeliveryMethodController;
use App\Http\Controllers\AdminApi\PaymentMethod\PaymentMethodController;

use Illuminate\Support\Facades\Route;


// =========================================================================
// PUBLIC ADMIN AUTH
// URL: /api/admin/login
// =========================================================================
Route::post('/login', [AdminAuthController::class, 'login']);

// =========================================================================
// PROTECTED ADMIN ENDPOINTS (Guard: api-admin)
// URL: /api/admin/profile, /api/admin/logout, etc.
// =========================================================================
Route::group(['middleware' => ['auth:api-admin']], function () {

    Route::get('/profile', function () {
        return response()->json(auth()->user());
    });

    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::apiResource('categories', CategoryController::class);

    Route::apiResource('titles', TitleController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('delivery-methods', DeliveryMethodController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);

    // Future Admin modules (Catalog Management, Analytics, Roles) go here
});
