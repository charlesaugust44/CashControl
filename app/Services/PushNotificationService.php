<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $vapid = config('webpush.vapid');

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => $vapid['subject'],
                'publicKey' => $vapid['public_key'],
                'privateKey' => $vapid['private_key'],
            ],
        ], [
            'TTL' => 86400,
        ]);
    }

    public function send(PushSubscription $pushSubscription, array $payload): bool
    {
        $subscription = Subscription::create([
            'endpoint' => $pushSubscription->endpoint,
            'keys' => [
                'p256dh' => $pushSubscription->p256dh,
                'auth' => $pushSubscription->auth,
            ],
        ]);

        $report = $this->webPush->sendOneNotification($subscription, json_encode($payload));

        if ($report->isSubscriptionExpired()) {
            $pushSubscription->delete();

            return false;
        }

        return $report->isSuccess();
    }

    public function sendBatch(array $notifications): void
    {
        foreach ($notifications as $notification) {
            $pushSubscription = $notification['subscription'];
            $payload = $notification['payload'];

            $subscription = Subscription::create([
                'endpoint' => $pushSubscription->endpoint,
                'keys' => [
                    'p256dh' => $pushSubscription->p256dh,
                    'auth' => $pushSubscription->auth,
                ],
            ]);

            $this->webPush->queueNotification($subscription, json_encode($payload));
        }

        $expiredEndpoints = [];

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $expiredEndpoints[] = $report->getEndpoint();
            }
        }

        if (! empty($expiredEndpoints)) {
            PushSubscription::whereIn('endpoint', $expiredEndpoints)->delete();
        }
    }

    public static function isConfigured(): bool
    {
        $vapid = config('webpush.vapid');

        return ! empty($vapid['public_key']) && ! empty($vapid['private_key']);
    }
}
