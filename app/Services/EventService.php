<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use App\Support\UnityContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;

class EventService
{
    private EventRepository $eventRepository;
    private EventGenerationService $eventGenerationService;

    public function __construct(UnityContext $unityContext)
    {
        $this->eventRepository = new EventRepository($unityContext);
        $this->eventGenerationService = new EventGenerationService($unityContext);
    }

    public function consolidate(int $id): Event
    {
        return $this->eventRepository->consolidate($id);
    }

    public function listByMonth(int $year, int $month): Enumerable
    {
        return $this->eventGenerationService->getMonthEvents($year, $month);
    }
}
