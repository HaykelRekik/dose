<?php

declare(strict_types=1);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/test', fn() => response()->json(
        [
            'version' => app()->version(),
            'locale' => app()->getLocale(),
            'text' => __('http-statuses.0'),
        ]
    ));
});
