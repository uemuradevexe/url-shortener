<?php

use App\Http\Controllers\Api\V1\ShortenUrlController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/shorten', ShortenUrlController::class)
        ->middleware('throttle:120,1');
});
