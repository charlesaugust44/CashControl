<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class RestoreService
{
    public function __construct(
        private GoogleDriveService $driveService,
    ) {}

    public function restore(int $backupId): void
    {
        $backup = DB::table('backups')->where('id', $backupId)->first();

        if (! $backup) {
            throw new \RuntimeException('Backup not found.');
        }

        if (! $backup->drive_file_id) {
            throw new \RuntimeException('Backup has no associated Drive file.');
        }

        $tempDir = storage_path('app/temp/restore_'.uniqid());
        mkdir($tempDir, 0755, true);

        $zipPath = $tempDir.'/backup.zip';

        try {
            $this->driveService->download($backup->drive_file_id, $zipPath);

            $this->extractZip($zipPath, $tempDir);

            DB::statement('PRAGMA foreign_keys = OFF');

            DB::transaction(function () use ($tempDir) {
                $deleteOrder = config('backup.delete_order');

                foreach ($deleteOrder as $table) {
                    DB::table($table)->truncate();
                }

                $restoreOrder = config('backup.restore_order');

                foreach ($restoreOrder as $table) {
                    $csvPath = $tempDir.'/'.$table.'.csv';

                    if (file_exists($csvPath)) {
                        $this->importCsvToTable($table, $csvPath);
                    }
                }
            });

            DB::statement('PRAGMA foreign_keys = ON');

            $this->cleanup($tempDir, $zipPath);
        } catch (\Throwable $e) {
            DB::statement('PRAGMA foreign_keys = ON');
            $this->cleanup($tempDir, $zipPath);

            Log::error('Restore failed: '.$e->getMessage());

            throw $e;
        }
    }

    private function extractZip(string $zipPath, string $destination): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("Cannot open zip: {$zipPath}");
        }

        $zip->extractTo($destination);
        $zip->close();
    }

    private function importCsvToTable(string $table, string $csvPath): void
    {
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open CSV: {$csvPath}");
        }

        $headers = fgetcsv($handle, 0, ',', '"', '\\');

        if ($headers === false) {
            fclose($handle);

            return;
        }

        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) !== count($headers)) {
                continue;
            }

            $row = array_combine($headers, $data);

            $row = array_map(function ($value) {
                return $value === '' ? null : $value;
            }, $row);

            DB::table($table)->insert($row);
        }

        fclose($handle);
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            if (is_dir($path)) {
                array_map('unlink', glob($path.'/*') ?: []);
                @rmdir($path);
            } elseif (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
