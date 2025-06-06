<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PhotoGridController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PhotoGridController::class, 'index']);
Route::get('/log', function () {
    return view('login');
}); 
Route::post('/login', [LoginController::class,'login'])->name('login');

// Photo Grid routes
Route::post('/save-photo-grid', [PhotoGridController::class, 'saveGrid'])->name('photo.save');
Route::get('/photo-grid/{id?}', [PhotoGridController::class, 'getGrid'])->name('photo.get');
Route::get('/photo-grids', [PhotoGridController::class, 'listGrids'])->name('photo.list');
Route::delete('/photo-grid/{id}', [PhotoGridController::class, 'deleteGrid'])->name('photo.delete');
Route::post('/export-photo-grid', [PhotoGridController::class, 'exportGrid'])->name('photo.export');

// Heroes routes
Route::get('/heroes', [App\Http\Controllers\HerosController::class, 'index'])->name('heroes.index');
Route::get('/heroes/create', [App\Http\Controllers\HerosController::class, 'create'])->name('heroes.create');
Route::post('/heroes', [App\Http\Controllers\HerosController::class, 'store'])->name('heroes.store');
Route::get('/heroes/list', [App\Http\Controllers\HerosController::class, 'list'])->name('heroes.list');
Route::get('/heroes/{id}', [App\Http\Controllers\HerosController::class, 'show'])->name('heroes.show');
Route::post('/heroes/{id}', [App\Http\Controllers\HerosController::class, 'update'])->name('heroes.update');
Route::delete('/heroes/{id}', [App\Http\Controllers\HerosController::class, 'destroy'])->name('heroes.destroy');

// Skins routes
Route::get('/heroes/{heroId}/skins', [App\Http\Controllers\SkinsController::class, 'manage'])->name('skins.manage');
Route::post('/skins', [App\Http\Controllers\SkinsController::class, 'store'])->name('skins.store');
Route::get('/skins/{heroId}', [App\Http\Controllers\SkinsController::class, 'index'])->name('skins.index');
Route::get('/all-skins', [App\Http\Controllers\SkinsController::class, 'getAllSkins'])->name('skins.all');
Route::delete('/skins/{heroId}/{skinId}', [App\Http\Controllers\SkinsController::class, 'destroy'])->name('skins.destroy');

// Test routes
Route::get('/test-database', [App\Http\Controllers\TestController::class, 'testDatabase'])->name('test.database');
Route::get('/cleanup-test', [App\Http\Controllers\TestController::class, 'cleanupTest'])->name('test.cleanup');