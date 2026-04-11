<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{

    protected EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService;
    }

    public function consolidate(int $id): JsonResponse
    {
        $event = $this->eventService->consolidate($id);

        return response()->json($event);
    }
}
