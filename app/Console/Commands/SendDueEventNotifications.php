<?php

namespace App\Console\Commands;

use App\Services\DueEventNotificationService;
use Illuminate\Console\Command;

class SendDueEventNotifications extends Command
{
    protected $signature = 'notifications:send-due-events';

    protected $description = 'Send push notifications for events due today or tomorrow';

    public function handle(DueEventNotificationService $service): int
    {
        $this->info('Sending due event notifications...');

        $service->send();

        $this->info('Done.');

        return self::SUCCESS;
    }
}
