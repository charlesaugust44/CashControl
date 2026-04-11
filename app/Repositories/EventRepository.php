<?php

namespace App\Repositories;

use App\Models\Event;

class EventRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(Event::class);
    }

    public function consolidate(int $id): Event
    {
        $event = Event::findOrFail($id);

        $event->consolidated = true;
        $event->save();

        return $event;
    }
}
