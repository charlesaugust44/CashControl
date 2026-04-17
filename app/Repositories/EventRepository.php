<?php

namespace App\Repositories;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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

    public function listByMonth(int $year, int $month): Collection
    {
        return Event::with(['entries.asset', 'header'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    }
}
