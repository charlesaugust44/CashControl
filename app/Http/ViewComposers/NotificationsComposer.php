<?php

namespace App\Http\ViewComposers;

use App\Helpers\Formatter;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class NotificationsComposer
{
    private BalanceService $balanceService;
    private Formatter $fmt;

    public function __construct()
    {
        $this->balanceService = new BalanceService();
        $this->fmt = new Formatter();
    }

    public function compose(View $view)
    {
        $unusualAlerts = $this->balanceService->getUnusualIncreases(10.0);

        $dismissedAlerts = Session::get('dismissed_alerts', []);
        $readAlerts = Session::get('read_alerts', []);

        $activeAlerts = $unusualAlerts->filter(function ($alert) use ($dismissedAlerts) {
            $key = $alert['header_id'] . '-' . $alert['event_id'];
            return !in_array($key, $dismissedAlerts);
        })->values();

        $unreadAlerts = $activeAlerts->filter(function ($alert) use ($readAlerts) {
            $key = $alert['header_id'] . '-' . $alert['event_id'];
            return !in_array($key, $readAlerts);
        })->values();

        $readActiveAlerts = $activeAlerts->filter(function ($alert) use ($readAlerts) {
            $key = $alert['header_id'] . '-' . $alert['event_id'];
            return in_array($key, $readAlerts);
        })->values();

        $cutoff = now()->subDays(45);
        $dismissedAlertItems = $unusualAlerts->filter(function ($alert) use ($dismissedAlerts, $cutoff) {
            $key = $alert['header_id'] . '-' . $alert['event_id'];
            return in_array($key, $dismissedAlerts) && $alert['date']->gte($cutoff);
        })->values();

        $view->with('notifications', [
            'unread' => $unreadAlerts,
            'read' => $readActiveAlerts,
            'dismissed' => $dismissedAlertItems,
            'total' => $unreadAlerts->count(),
            'fmt' => $this->fmt,
        ]);
    }
}
