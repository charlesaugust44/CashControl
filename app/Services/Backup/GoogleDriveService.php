<?php

namespace App\Services\Backup;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\DB;

class GoogleDriveService
{
    private function getClient(): Client
    {
        $token = DB::table('google_drive_tokens')->latest()->first();

        if (! $token) {
            throw new \RuntimeException('Google Drive is not connected.');
        }

        $client = new Client;
        $client->setClientId(config('backup.google.client_id'));
        $client->setClientSecret(config('backup.google.client_secret'));
        $client->setRedirectUri(config('backup.google.redirect_uri'));
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $client->setAccessToken([
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_in' => $token->expires_at,
            'created' => $token->token_created_at,
        ]);

        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->fetchAccessTokenWithRefreshToken($token->refresh_token);

            if (isset($refreshToken['error'])) {
                throw new \RuntimeException('Failed to refresh token: '.$refreshToken['error']);
            }

            DB::table('google_drive_tokens')->where('id', $token->id)->update([
                'access_token' => $refreshToken['access_token'],
                'expires_at' => $refreshToken['expires_in'] ?? null,
                'token_created_at' => $refreshToken['created'] ?? time(),
                'updated_at' => now(),
            ]);
        }

        return $client;
    }

    public function getDriveService(): Drive
    {
        return new Drive($this->getClient());
    }

    public function upload(string $filePath, string $filename): DriveFile
    {
        $drive = $this->getDriveService();

        $fileMetadata = new DriveFile([
            'name' => $filename,
            'mimeType' => 'application/zip',
        ]);

        $folderId = config('backup.google.backup_folder_id');
        if ($folderId) {
            $fileMetadata->setParents([$folderId]);
        }

        $content = file_get_contents($filePath);

        $file = $drive->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/zip',
            'uploadType' => 'multipart',
            'fields' => 'id,name,size,createdTime',
        ]);

        return $file;
    }

    public function download(string $fileId, string $destinationPath): void
    {
        $drive = $this->getDriveService();
        $response = $drive->files->get($fileId, ['alt' => 'media']);
        file_put_contents($destinationPath, $response->getBody()->getContents());
    }

    public function delete(string $fileId): void
    {
        $drive = $this->getDriveService();
        $drive->files->delete($fileId);
    }

    public function isConnected(): bool
    {
        return DB::table('google_drive_tokens')->exists();
    }

    public function disconnect(): void
    {
        DB::table('google_drive_tokens')->truncate();
    }

    public function getAuthUrl(): string
    {
        $client = new Client;
        $client->setClientId(config('backup.google.client_id'));
        $client->setClientSecret(config('backup.google.client_secret'));
        $client->setRedirectUri(config('backup.google.redirect_uri'));
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client->createAuthUrl();
    }

    public function handleCallback(string $code): void
    {
        $client = new Client;
        $client->setClientId(config('backup.google.client_id'));
        $client->setClientSecret(config('backup.google.client_secret'));
        $client->setRedirectUri(config('backup.google.redirect_uri'));
        $client->addScope(Drive::DRIVE_FILE);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException('OAuth error: '.$token['error']);
        }

        DB::table('google_drive_tokens')->truncate();

        DB::table('google_drive_tokens')->insert([
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at' => $token['expires_in'] ?? null,
            'token_created_at' => $token['created'] ?? time(),
            'connected_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function listBackupFiles(): array
    {
        $drive = $this->getDriveService();

        $query = "mimeType='application/zip' and name contains 'backup_'";
        $folderId = config('backup.google.backup_folder_id');
        if ($folderId) {
            $query .= " and '{$folderId}' in parents";
        }

        $response = $drive->files->listFiles([
            'q' => $query,
            'fields' => 'files(id,name,size,createdTime)',
            'orderBy' => 'createdTime desc',
            'pageSize' => 100,
        ]);

        return $response->getFiles();
    }
}
