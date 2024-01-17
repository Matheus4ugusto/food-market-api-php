<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix("/v1")->group(function () {
    Route::post("/register", [AuthController::class, 'register']);
    Route::post("/login", [AuthController::class,  'login']);
    Route::delete("/logout", [AuthController::class, 'logout']);

    Route::prefix('/stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{store_id}', [StoreController::class, 'show']);
        Route::post('/', [StoreController::class, 'store'])
            ->middleware(['auth:api', 'permission:ADMIN,MANAGER,SELLER']);
        Route::put('/{store_id}', [StoreController::class, 'update'])
            ->middleware(['auth:api'], 'permission:ADMIN,MANAGER,SELLER');
        Route::delete('/{store_id}', [StoreController::class, 'delete'])
            ->middleware(['auth:api'], 'permission:ADMIN,MANAGER,SELLER');
    });

    Route::prefix('/product')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product_id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{product_id}', [ProductController::class, 'update']);
        Route::delete('/{product_id}', [ProductController::class, 'destroy']);
    });

    Route::prefix('/orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::post(
            '/{order_id}/checkout',
            [OrderController::class, 'checkout']
        );
        Route::post('/webhook', [OrderController::class, 'webhook']);
    });
});
