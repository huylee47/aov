<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('photo');
});
Route::get('/log', function () {
    return view('login');
}); 
Route::post('/login', [LoginController::class,'login'])->name('login');