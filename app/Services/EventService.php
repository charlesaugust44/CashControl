<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;

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
}
