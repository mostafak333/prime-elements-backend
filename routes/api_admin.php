<?php

use App\Http\Controllers\AdminApi\Auth\AdminAuthController;
use App\Http\Controllers\AdminApi\Category\AdminCategoryController;
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
    Route::apiResource('categories', AdminCategoryController::class);

    Route::apiResource('titles', TitleController::class);

    // Future Admin modules (Catalog Management, Analytics, Roles) go here
});
