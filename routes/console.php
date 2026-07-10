<?php

use App\Services\Backup\BackupService;
use App\Services\Backup\GoogleDriveService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Log::info('Scheduled backup task started', ['time' => now()->toDateTimeString()]);
    
    $driveService = app(GoogleDriveService::class);

    if (! $driveService->isConnected()) {
        Log::warning('Scheduled backup skipped: Google Drive is not connected');
        return;
    }

    try {
        Log::info('Starting backup creation');
        $backupService = app(BackupService::class);
        $backupService->create('scheduled');
        Log::info('Scheduled backup completed successfully');
    } catch (\Throwable $e) {
        Log::error('Scheduled backup failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
})->dailyAt(config('backup.schedule.time', '02:00'));
