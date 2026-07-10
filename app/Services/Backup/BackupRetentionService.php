<?php

namespace App\Services\Backup;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupRetentionService
{
    public function __construct(
        private GoogleDriveService $driveService,
    ) {}

    public function cleanup(): int
    {
        $deleted = 0;

        $deleted += $this->purgeFailedBackups();
        $deleted += $this->applyRetention();

        return $deleted;
    }

    private function purgeFailedBackups(): int
    {
        $failed = DB::table('backups')
            ->where('status', 'failed')
            ->get();

        $count = 0;

        foreach ($failed as $backup) {
            DB::table('backups')->where('id', $backup->id)->delete();
            $count++;
        }

        return $count;
    }

    private function applyRetention(): int
    {
        $config = config('backup.retention');
        $dailyDays = $config['daily_days'];
        $weeklyDays = $config['weekly_days'];
        $monthlyDays = $config['monthly_days'];

        $now = Carbon::now();
        $dailyCutoff = $now->copy()->subDays($dailyDays);
        $weeklyCutoff = $now->copy()->subDays($dailyDays + $weeklyDays);
        $monthlyCutoff = $now->copy()->subDays($dailyDays + $weeklyDays + $monthlyDays);

        $backups = DB::table('backups')
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->get();

        $keep = [];
        $delete = [];

        foreach ($backups as $backup) {
            $createdAt = Carbon::parse($backup->created_at);
            $ageInDays = $now->diffInDays($createdAt);

            if ($createdAt->lt($monthlyCutoff)) {
                $delete[] = $backup;

                continue;
            }

            if ($createdAt->gte($dailyCutoff)) {
                $key = 'daily:'.$createdAt->format('Y-m-d');
            } elseif ($createdAt->gte($weeklyCutoff)) {
                $key = 'weekly:'.$createdAt->format('o-W');
            } else {
                $key = 'monthly:'.$createdAt->format('Y-m');
            }

            if (! isset($keep[$key])) {
                $keep[$key] = $backup;
            } else {
                $delete[] = $backup;
            }
        }

        $deletedCount = 0;

        foreach ($delete as $backup) {
            $this->deleteBackup($backup);
            $deletedCount++;
        }

        return $deletedCount;
    }

    private function deleteBackup(object $backup): void
    {
        try {
            if ($backup->drive_file_id) {
                $this->driveService->delete($backup->drive_file_id);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to delete backup from Drive: {$e->getMessage()}");
        }

        DB::table('backups')->where('id', $backup->id)->delete();
    }
}
