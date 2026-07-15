<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $user = $request->user();

        $subscription = $user->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'p256dh' => $validated['keys']['p256dh'],
                'auth' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aesgcm',
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['id' => $subscription->id]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $request->user()
            ->pushSubscriptions()
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
