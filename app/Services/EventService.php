<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventService
{
    private EventRepository $eventRepository;
    private EventGenerationService $eventGenerationService;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $this->eventGenerationService = new EventGenerationService();
    }

    public function consolidate(int $id): Event
    {
        return $this->eventRepository->consolidate($id);
    }

    public function listByMonth(int $year, int $month): Collection
    {
        return $this->eventGenerationService->getMonthEvents($year, $month);
    }
}
