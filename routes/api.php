<?php

use App\Http\Controllers\Auth\AuthController;
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
        Route::get('/{store_id}',[StoreController::class, 'show']);
        Route::post('/', [StoreController::class, 'store'])
            ->middleware(['auth:api', 'permission:ADMIN,MANAGER,SELLER']);
        Route::put('/{store_id}', [StoreController::class, 'update'])
            ->middleware(['auth:api'], 'permission:ADMIN,MANAGER,SELLER');
        Route::delete('/{store_id}', [StoreController::class, 'delete'])
            ->middleware(['auth:api'], 'permission:ADMIN,MANAGER,SELLER');
    });
});
