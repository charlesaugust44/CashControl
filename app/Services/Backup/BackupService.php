<?php

namespace App\Services\Backup;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class BackupService
{
    public function __construct(
        private GoogleDriveService $driveService,
        private BackupRetentionService $retentionService,
    ) {}

    public function create(string $triggerType = 'manual'): array
    {
        $tables = config('backup.tables');
        $timestamp = now()->format('Ymd_His');
        $filename = "backup_{$timestamp}.zip";
        $tempDir = storage_path('app/temp/backup_'.uniqid());

        mkdir($tempDir, 0755, true);

        foreach ($tables as $table) {
            $this->exportTableToCsv($table, $tempDir.'/'.$table.'.csv');
        }

        $zipPath = storage_path('app/temp/'.$filename);
        $this->createZip($tempDir, $zipPath);

        $driveFile = $this->driveService->upload($zipPath, $filename);

        $sizeBytes = filesize($zipPath);

        $backupId = DB::table('backups')->insertGetId([
            'filename' => $filename,
            'drive_file_id' => $driveFile->id,
            'size_bytes' => $sizeBytes,
            'trigger_type' => $triggerType,
            'created_by' => auth()->id(),
            'status' => 'success',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->cleanup($tempDir, $zipPath);

        $retentionDeleted = 0;
        try {
            $retentionDeleted = $this->retentionService->cleanup();
        } catch (\Throwable $e) {
            Log::warning('Retention cleanup failed: '.$e->getMessage());
        }

        return [
            'id' => $backupId,
            'filename' => $filename,
            'drive_file_id' => $driveFile->id,
            'size_bytes' => $sizeBytes,
            'retention_deleted' => $retentionDeleted,
        ];
    }

    public function syncBackups(): int
    {
        $driveFiles = $this->driveService->listBackupFiles();
        $existingBackups = DB::table('backups')->pluck('drive_file_id')->toArray();

        $synced = 0;
        foreach ($driveFiles as $file) {
            if (! in_array($file->getId(), $existingBackups)) {
                $createdAt = Carbon::parse($file->getCreatedTime())->format('Y-m-d H:i:s');

                DB::table('backups')->insert([
                    'filename' => $file->getName(),
                    'drive_file_id' => $file->getId(),
                    'size_bytes' => $file->getSize(),
                    'trigger_type' => 'synced',
                    'created_by' => auth()->id(),
                    'status' => 'success',
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);
                $synced++;
            }
        }

        return $synced;
    }

    private function exportTableToCsv(string $table, string $filePath): void
    {
        $rows = DB::table($table)->get();

        $handle = fopen($filePath, 'w');

        $columns = Schema::getColumnListing($table);
        fputcsv($handle, $columns, ',', '"', '\\');

        foreach ($rows as $row) {
            fputcsv($handle, (array) $row, ',', '"', '\\');
        }

        fclose($handle);
    }

    private function createZip(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Cannot create zip: {$zipPath}");
        }

        foreach (glob($sourceDir.'/*.csv') as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            if (is_dir($path)) {
                array_map('unlink', glob($path.'/*'));
                rmdir($path);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }
    }
}
