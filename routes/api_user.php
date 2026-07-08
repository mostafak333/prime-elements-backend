<?php


use App\Http\Controllers\UserApi\Product\ProductController;
use App\Http\Controllers\UserApi\Auth\UserAuthController;
use App\Http\Controllers\UserApi\Category\CategoryController;
use App\Http\Controllers\UserApi\Title\TitleController;
use App\Http\Controllers\UserApi\Wishlist\WishlistController;
use App\Http\Controllers\UserApi\Cart\CartController;
use App\Http\Controllers\UserApi\Order\OrderController;
use Illuminate\Support\Facades\Route;


// =========================================================================
// PUBLIC CUSTOMER AUTH
// =========================================================================
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::get('/verify-email/{token}', [UserAuthController::class, 'verifyEmail']);
Route::post('/forgot-password', [UserAuthController::class, 'forgotPassword']);
Route::post('/reset-password', [UserAuthController::class, 'resetPassword']);

// =========================================================================
// PROTECTED CUSTOMER ENDPOINTS (Guard: api-user)
// =========================================================================
Route::group(['middleware' => ['auth:api-user']], function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/filter-options', [ProductController::class, 'filterOptions']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('titles', [TitleController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get(
        'categories/{category}/subcategories',
        [CategoryController::class, 'subCategories']
    );
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::post('/change-password', [UserAuthController::class, 'changePassword']);
    Route::get('/profile', [UserAuthController::class, 'profile']);
    Route::put('/profile', [UserAuthController::class, 'updateProfile']);

    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist', [WishlistController::class, 'store']);
    Route::delete('wishlist/{wishlist}', [WishlistController::class, 'destroy']);

    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{cart_item}', [CartController::class, 'update']);
    Route::delete('cart', [CartController::class, 'clear']);
    Route::delete('cart/{cart_item}', [CartController::class, 'destroy']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
});
