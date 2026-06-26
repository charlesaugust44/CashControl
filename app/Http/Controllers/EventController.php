<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{

    protected EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService;
    }

    public function index(Request $request): View
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $monthDate = \Carbon\Carbon::parse($currentMonth);

        $events = $this->eventService->listByMoth(
            $monthDate->year,
            $monthDate->month
        );

        return view('entries.index', [
            'events' => $events,
            'currentMonth' => $currentMonth,
            'pageTitle' => 'Entries',
        ]);
    }

    public function consolidate(int $id): JsonResponse
    {
        $event = $this->eventService->consolidate($id);

        return response()->json($event);
    }

    public function show(int $year, int $month)
    {
        $events = $this->eventService->listByMoth($year, $month);

        return response()->json($events);
    }
}
