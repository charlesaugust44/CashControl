<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return view('dashboard', ['pageTitle' => 'Dashboard']);
});

Route::get('/assets', [AssetController::class, 'index']);
Route::get('/assets/create', [AssetController::class, 'create']);
Route::get('/assets/{id}', [AssetController::class, 'show']);
Route::get('/assets/{id}/edit', [AssetController::class, 'edit']);
Route::post('/assets', [AssetController::class, 'store']);
Route::put('/assets/{id}', [AssetController::class, 'update']);

Route::get('/entries', [EventController::class, 'index']);
