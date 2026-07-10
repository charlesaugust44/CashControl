<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', rtrim(env('APP_URL'), '/').'/admin/backups/callback'),
        'backup_folder_id' => env('GOOGLE_DRIVE_BACKUP_FOLDER_ID'),
    ],

    'tables' => [
        'users',
        'unities',
        'unity_user',
        'assets',
        'headers',
        'events',
        'entries',
    ],

    'restore_order' => [
        'users',
        'unities',
        'unity_user',
        'assets',
        'headers',
        'events',
        'entries',
    ],

    'delete_order' => [
        'entries',
        'events',
        'headers',
        'assets',
        'unity_user',
        'unities',
        'users',
    ],

    'schedule' => [
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'),
    ],

    'retention' => [
        'daily_days' => 7,
        'weekly_days' => 28,
        'monthly_days' => 120,
    ],
];
