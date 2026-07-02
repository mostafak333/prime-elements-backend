<?php


use App\Http\Controllers\UserApi\Auth\UserAuthController;
use App\Http\Controllers\UserApi\Title\TitleController;

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
    
    Route::post('/logout', [UserAuthController::class, 'logout']);

    // Future Customer modules (Cart, Wishlist, Orders) go here
});
