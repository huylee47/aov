<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Group các route API với middleware 'api'
Route::middleware('api')->group(function () {
    // Route cho đăng nhập
    Route::post('/login', [LoginController::class, 'login']);
});