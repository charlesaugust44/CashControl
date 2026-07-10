<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\GoogleDriveAuthController;
use App\Http\Controllers\Admin\UnityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventDetailController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\MonthClosureController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/password/forgot', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::get('/pending-approval', function () {
    if (auth()->check() && auth()->user()->isApproved()) {
        return redirect('/');
    }

    return view('auth.pending-approval');
})->middleware('auth')->name('pending-approval');

Route::get('/no-unity', function () {
    if (auth()->guest()) {
        return redirect()->route('login');
    }
    if (auth()->user()->unities->count() > 0) {
        return redirect('/');
    }

    return view('auth.no-unity');
})->middleware('auth')->name('no-unity');

Route::middleware(['auth', 'approved', 'has.unity'])->group(function () {
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
    Route::get('/templates/{id}/edit', [HeaderController::class, 'edit']);
    Route::put('/templates/{id}', [HeaderController::class, 'update']);
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
});

Route::middleware(['auth', 'approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{id}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::put('/users/{id}/role', [UserController::class, 'updateRole'])->name('users.updateRole');

    Route::get('/unities/create', [UnityController::class, 'create'])->name('unities.create');
    Route::post('/unities', [UnityController::class, 'store'])->name('unities.store');
    Route::get('/unities/{id}/edit', [UnityController::class, 'edit'])->name('unities.edit');
    Route::put('/unities/{id}', [UnityController::class, 'update'])->name('unities.update');
    Route::delete('/unities/{id}', [UnityController::class, 'destroy'])->name('unities.destroy');
    Route::post('/unities/{id}/assign', [UnityController::class, 'assign'])->name('unities.assign');
    Route::post('/unities/{id}/unassign/{userId}', [UnityController::class, 'unassign'])->name('unities.unassign');
    Route::get('/unities/{id}', [UnityController::class, 'show'])->name('unities.show');
    Route::get('/unities', [UnityController::class, 'index'])->name('unities.index');

    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'create'])->name('backups.store');
    Route::get('/backups/connect', [GoogleDriveAuthController::class, 'connect'])->name('backups.connect');
    Route::get('/backups/callback', [GoogleDriveAuthController::class, 'callback'])->name('backups.callback');
    Route::delete('/backups/disconnect', [GoogleDriveAuthController::class, 'disconnect'])->name('backups.disconnect');
    Route::post('/backups/{id}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('/backups/{id}', [BackupController::class, 'destroy'])->name('backups.destroy');
});
