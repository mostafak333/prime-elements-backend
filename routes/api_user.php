<?php


use App\Http\Controllers\UserApi\Auth\UserAuthController;
use App\Http\Controllers\UserApi\Title\TitleController;
use App\Http\Controllers\UserApi\Category\CategoryController;

use Illuminate\Support\Facades\Route;


// =========================================================================
// PUBLIC CUSTOMER AUTH
// =========================================================================
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);

// =========================================================================
// PROTECTED CUSTOMER ENDPOINTS (Guard: api-user)
// URL: /api/user, /api/logout, etc.
// =========================================================================
Route::group(['middleware' => ['auth:api-user']], function () {

    Route::get('/user', function () {
        return response()->json(auth()->user());
    });

    Route::get('titles', [TitleController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get(
        'categories/{category}/subcategories',
        [CategoryController::class, 'subCategories']
    );
    Route::post('/logout', [UserAuthController::class, 'logout']);

    // Future Customer modules (Cart, Wishlist, Orders) go here
});
