<?php

declare(strict_types=1);

use App\Http\Controllers\API\Auth\OTPController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/test', fn () => response()->json(
    [
        'version' => app()->version(),
        'locale' => app()->getLocale(),
        'text' => __('http-statuses.0'),
    ]
));

Route::prefix('categories')->group(function (): void {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category:slug}', [CategoryController::class, 'show']);
});

Route::prefix('products')->group(function (): void {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
});

Route::prefix('auth')->group(function (): void {
    // OTP - Login //
    Route::prefix('otp')->middleware('throttle:5,1')->group(function (): void {
        Route::post('request', [OTPController::class, 'requestOtp']);
        Route::post('verify', [OTPController::class, 'verifyOtp']);
    });
});
