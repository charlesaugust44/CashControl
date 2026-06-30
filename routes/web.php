<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventDetailController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\MonthClosureController;

Route::get('/', [DashboardController::class, 'index']);
Route::post('/dashboard/dismiss', [DashboardController::class, 'dismiss']);
Route::post('/notifications/mark-read', [DashboardController::class, 'markRead']);

Route::get('/assets', [AssetController::class, 'index']);
Route::get('/assets/create', [AssetController::class, 'create']);
Route::get('/assets/{id}', [AssetController::class, 'show']);
Route::get('/assets/{id}/edit', [AssetController::class, 'edit']);
Route::post('/assets', [AssetController::class, 'store']);
Route::put('/assets/{id}', [AssetController::class, 'update']);

Route::get('/templates', [HeaderController::class, 'index']);
Route::get('/templates/create', [HeaderController::class, 'create']);
Route::post('/templates', [HeaderController::class, 'store']);
Route::get('/templates/{id}', [HeaderController::class, 'show']);
Route::get('/templates/{id}/edit', [HeaderController::class, 'edit']);
Route::put('/templates/{id}', [HeaderController::class, 'update']);
Route::get('/templates/{id}/delete', [HeaderController::class, 'delete']);
Route::delete('/templates/{id}', [HeaderController::class, 'destroy']);

Route::get('/entries', [EventController::class, 'index']);
Route::get('/entries/create', [EventDetailController::class, 'create']);
Route::post('/entries', [EventDetailController::class, 'storeStandalone']);
Route::get('/entries/virtual/{headerId}/{year}/{month}', [EventDetailController::class, 'showVirtual']);
Route::post('/entries/virtual/{headerId}/{year}/{month}', [EventDetailController::class, 'store']);
Route::get('/entries/{id}', [EventDetailController::class, 'show']);
Route::put('/entries/{id}', [EventDetailController::class, 'update']);
Route::delete('/entries/{id}', [EventDetailController::class, 'destroy']);

Route::post('/months/{year}/{month}/close', [MonthClosureController::class, 'close']);
Route::post('/months/reopen', [MonthClosureController::class, 'reopen']);
