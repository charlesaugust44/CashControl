<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Backup\BackupService;
use App\Services\Backup\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleDriveAuthController extends Controller
{
    public function __construct(
        private GoogleDriveService $driveService,
        private BackupService $backupService,
    ) {}

    public function connect(): RedirectResponse
    {
        $url = $this->driveService->getAuthUrl();

        return redirect($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $this->driveService->handleCallback($request->get('code'));

            $synced = $this->backupService->syncBackups();

            $message = $synced > 0
                ? __('admin.backups.drive_connected_with_sync', ['count' => $synced])
                : __('admin.backups.drive_connected_success');

            return redirect()
                ->route('admin.backups.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', __('admin.backups.drive_connection_failed', ['error' => $e->getMessage()]));
        }
    }

    public function disconnect(): RedirectResponse
    {
        $this->driveService->disconnect();

        return redirect()
            ->route('admin.backups.index')
            ->with('success', __('admin.backups.drive_disconnected'));
    }
}
