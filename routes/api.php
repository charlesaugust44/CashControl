<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\EventController;


Route::middleware(['auth', 'approved', 'has.unity', \App\Http\Middleware\SetUnityContext::class])->group(function () {
    Route::prefix('assets')->group(function () {
        Route::get('/', [AssetController::class, 'index']);
        Route::get('/{id}', [AssetController::class, 'show']);
        Route::get('/{id}/entries', [AssetController::class, 'entries']);
        Route::post('/', [AssetController::class, 'store']);
        Route::put('/{id}', [AssetController::class, 'update']);
    });

    Route::prefix('headers')->group(function () {
        Route::get('/', [HeaderController::class, 'index']);
    });

    Route::prefix('events')->group(function () {
        Route::patch('/{id}/consolidate', [EventController::class, 'consolidate']);
        Route::get('/{year}/{month}', [EventController::class, 'show'])
            ->where([
                'year' => '20[0-9]{2}|[0-9]{4}',
                'month' => '0?[1-9]|1[0-2]'
            ]);
    });
});
