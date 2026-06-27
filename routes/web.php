<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MonthClosureController;

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
Route::patch('/events/{id}/consolidate', [EventController::class, 'consolidate']);
Route::patch('/events/{id}/unconsolidate', [EventController::class, 'unconsolidate']);

Route::post('/months/{year}/{month}/close', [MonthClosureController::class, 'close']);
Route::post('/months/reopen', [MonthClosureController::class, 'reopen']);
