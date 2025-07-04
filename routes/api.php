<?php

declare(strict_types=1);

use App\Http\Controllers\API\CategoryController;

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
