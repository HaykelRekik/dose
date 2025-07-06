<?php

declare(strict_types=1);

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')->group(function (): void {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category:slug}', [CategoryController::class, 'show']);
});

Route::prefix('products')->group(function (): void {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
});

Route::prefix('auth')->group(function (): void {
    Route::middleware('throttle:5,1')->group(function (): void {
        // OTP - Login //
        Route::prefix('otp')->group(function (): void {
            Route::post('request', [LoginController::class, 'requestOtp']);
            Route::post('verify', [LoginController::class, 'verifyOtp']);
        });

        // Registration //
        Route::post('register', [AuthController::class, 'register']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('profile', [AuthController::class, 'updateProfile']);
    });

});

Route::prefix('branches')->group(function (): void {
    Route::get('/', [BranchController::class, 'index']);
    Route::get('/{branch}', [BranchController::class, 'show']);
});
