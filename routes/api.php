<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\EventController;


Route::prefix('assets')->group(function () {
    Route::get('/', [AssetController::class, 'index']);
    Route::get('/{id}/entries', [AssetController::class, 'entries']);
});

Route::prefix('headers')->group(function () {
    Route::get('/', [HeaderController::class, 'index']);
});

Route::prefix('events')->group(function () {
    Route::PATCH('/{id}/consolidate', [EventController::class, 'consolidate']);
});
