<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Backup\BackupService;
use App\Services\Backup\GoogleDriveService;
use App\Services\Backup\RestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService,
        private RestoreService $restoreService,
        private GoogleDriveService $driveService,
    ) {}

    public function index(): View
    {
        $backups = DB::table('backups')
            ->orderBy('created_at', 'desc')
            ->get();

        $isConnected = $this->driveService->isConnected();

        return view('admin.backups.index', [
            'backups' => $backups,
            'isConnected' => $isConnected,
            'pageTitle' => __('admin.backups.title'),
            'breadcrumbs' => [
                ['label' => __('admin.backups.title'), 'url' => null],
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        if (! $this->driveService->isConnected()) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', __('admin.backups.drive_not_connected'));
        }

        try {
            $result = $this->backupService->create('manual');

            if ($result['retention_deleted'] > 0) {
                return redirect()
                    ->route('admin.backups.index')
                    ->with('success', __('admin.backups.backup_created_with_retention', ['count' => $result['retention_deleted']]));
            }

            return redirect()
                ->route('admin.backups.index')
                ->with('success', __('admin.backups.backup_created'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', __('admin.backups.backup_failed', ['error' => $e->getMessage()]));
        }
    }

    public function restore(int $id): RedirectResponse
    {
        if (! $this->driveService->isConnected()) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', __('admin.backups.drive_not_connected'));
        }

        try {
            $this->restoreService->restore($id);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', __('admin.backups.restore_completed'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', __('admin.backups.restore_failed', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $backup = DB::table('backups')->where('id', $id)->first();

        if (! $backup) {
            abort(404);
        }

        if ($backup->drive_file_id) {
            try {
                $this->driveService->delete($backup->drive_file_id);
            } catch (\Throwable $e) {
            }
        }

        DB::table('backups')->where('id', $id)->delete();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', __('admin.backups.backup_deleted'));
    }
}
