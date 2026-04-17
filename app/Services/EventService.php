<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventService
{
    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
    }

    public function consolidate(int $id): Event
    {
        return $this->eventRepository->consolidate($id);
    }

    public function listByMoth(int $year, int $month): Collection
    {
        return $this->eventRepository->listByMonth($year, $month);
    }
}
