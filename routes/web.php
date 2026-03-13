<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'URL Shortener API',
        'create_endpoint' => '/api/v1/shorten',
    ]);
});

Route::get('/{short_code}', RedirectController::class)
    ->where('short_code', '[0-9A-Za-z]+');
