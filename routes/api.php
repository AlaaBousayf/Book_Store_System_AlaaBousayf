<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Author\AuthController as AuthorAuthController;
use App\Http\Controllers\Author\BookController;
use App\Http\Controllers\Author\CategoryController as AuthorCategoryController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\BookController as CustomerBookController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CategoryController as CustomerCategoryController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthorMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



use App\Http\Controllers\Author\CoAuthorController;
use App\Http\Controllers\Author\OrderController as AuthorOrderController;

Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});

Route::prefix('admin')->middleware(['auth:sanctum',AdminMiddleware::class])->group(function(){
    Route::apiResource('category',CategoryController::class);
    Route::apiResource('author',AuthorController::class);
    Route::put('author/{author}/approve',[AuthorController::class,'approve']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::apiResource('payment-method', PaymentMethodController::class);
    Route::get('users', [UserController::class, 'index']);
    Route::put('users/{user}/block', [UserController::class, 'block']);
});


Route::post('customer/sign-up',[CustomerAuthController::class,'signup']);
Route::prefix('customer')->middleware(['auth:sanctum',CustomerMiddleware::class])->group(function(){
    Route::apiResource('book',CustomerBookController::class)->only(['index','show']);
    Route::apiResource('category',CustomerCategoryController::class)->only('index');
    Route::apiResource('cart',CartController::class)->except(['store', 'destroy']);
    Route::post('cart/{book}',[CartController::class,'store']);
    Route::delete('cart/{book}',[CartController::class,'destroy']);
    Route::post('checkout', [CustomerOrderController::class, 'store']);
    Route::get('orders', [CustomerOrderController::class, 'index']);
    Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
});

Route::post('author/sign-up',[AuthorAuthController::class,'signup']);
Route::prefix('author')->middleware(['auth:sanctum',AuthorMiddleware::class])->group(function(){
    Route::apiResource('book',BookController::class);
    Route::put('book/{book}/publish', [BookController::class, 'publish']);
    Route::put('book/{book}/stock', [BookController::class, 'updateStock']);
    Route::apiResource('category',AuthorCategoryController::class)->only('index');
    Route::get('orders', [AuthorOrderController::class, 'index']);

    Route::post('co-author/join', [CoAuthorController::class, 'join']);
    Route::get('co-author/requests', [CoAuthorController::class, 'requests']);
    Route::put('co-author/requests/{book}/{user}/approve', [CoAuthorController::class, 'approve']);
    Route::put('co-author/requests/{book}/{user}/reject', [CoAuthorController::class, 'reject']);
});






