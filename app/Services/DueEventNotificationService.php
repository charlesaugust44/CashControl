<?php

namespace App\Services;

use App\Helpers\Formatter;
use App\Models\Event;
use App\Models\PushNotificationLog;
use App\Models\PushSubscription;
use App\Models\User;
use App\Support\UnityContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DueEventNotificationService
{
    private UnityContext $unityContext;

    private EventGenerationService $eventGenerationService;

    private Formatter $formatter;

    public function __construct()
    {
        $this->unityContext = new UnityContext;
        $this->formatter = new Formatter;
    }

    public function send(): void
    {
        if (! PushNotificationService::isConfigured()) {
            Log::warning('Push notifications not configured (VAPID keys missing)');

            return;
        }

        $today = Carbon::today();
        $targets = $this->buildTargets($today);

        $usersWithSubscriptions = User::whereHas('pushSubscriptions')->with('pushSubscriptions')->get();

        foreach ($usersWithSubscriptions as $user) {
            $this->notifyUser($user, $targets);
        }
    }

    private function buildTargets(Carbon $today): array
    {
        $targets = [];

        $targets[] = [
            'date' => $today->copy(),
            'type' => 'due_date',
            'year' => $today->year,
            'month' => $today->month,
            'day' => $today->day,
        ];

        $tomorrow = $today->copy()->addDay();
        $targets[] = [
            'date' => $tomorrow,
            'type' => 'day_before',
            'year' => $tomorrow->year,
            'month' => $tomorrow->month,
            'day' => $tomorrow->day,
        ];

        return $targets;
    }

    private function notifyUser(User $user, array $targets): void
    {
        $unity = $user->unity();
        if (! $unity) {
            return;
        }

        $this->unityContext->set($unity->id);
        $this->eventGenerationService = new EventGenerationService($this->unityContext);

        $notifications = [];

        foreach ($targets as $target) {
            $events = $this->getUnconsolidatedEventsForDay(
                $target['year'],
                $target['month'],
                $target['day']
            );

            foreach ($events as $event) {
                $eventKey = $this->eventKey($event);
                $notificationType = $target['type'];

                if ($this->isAlreadyLogged($user->id, $eventKey, $notificationType)) {
                    continue;
                }

                $payload = $this->buildPayload($event, $target, $eventKey);

                foreach ($user->pushSubscriptions as $subscription) {
                    $notifications[] = [
                        'subscription' => $subscription,
                        'payload' => $payload,
                    ];
                }

                PushNotificationLog::create([
                    'user_id' => $user->id,
                    'event_key' => $eventKey,
                    'notification_type' => $notificationType,
                ]);
            }
        }

        if (! empty($notifications)) {
            $pushService = new PushNotificationService;
            $pushService->sendBatch($notifications);
        }

        $this->unityContext->clear();
    }

    private function getUnconsolidatedEventsForDay(int $year, int $month, int $day): Collection
    {
        $allEvents = $this->eventGenerationService->getMonthEvents($year, $month);

        return $allEvents->filter(function ($event) use ($day) {
            if ($event->due_day !== $day) {
                return false;
            }

            return ! $event->isFullyConsolidated();
        });
    }

    private function eventKey($event): string
    {
        $isVirtual = $event->id === 0 || $event->id === null;

        if ($isVirtual) {
            return 'virtual_'.$event->header_id.'_'.$event->date->format('Y').'_'.$event->date->format('m');
        }

        return 'event_'.$event->id;
    }

    private function isAlreadyLogged(int $userId, string $eventKey, string $notificationType): bool
    {
        return PushNotificationLog::where('user_id', $userId)
            ->where('event_key', $eventKey)
            ->where('notification_type', $notificationType)
            ->exists();
    }

    private function buildPayload($event, array $target, string $eventKey): array
    {
        $isDueDate = $target['type'] === 'due_date';
        $title = $isDueDate ? 'Event due today' : 'Event due tomorrow';
        $amount = $this->formatter->currency($event->getDisplayAmount());
        $dueDateStr = $this->formatter->date($target['date']);
        $name = $event->name ?? 'Unnamed';

        $body = "{$name} - {$amount} ({$dueDateStr})";

        $isVirtual = $event->id === 0 || $event->id === null;
        $url = $isVirtual
            ? url('/entries/virtual/'.$event->header_id.'/'.$event->date->format('Y').'/'.$event->date->format('m'))
            : url('/entries/'.$event->id);

        return [
            'title' => $title,
            'body' => $body,
            'icon' => '/icons/icon-192.png',
            'badge' => '/icons/icon-192.png',
            'url' => $url,
            'tag' => $target['type'].'-'.$eventKey,
        ];
    }
}
