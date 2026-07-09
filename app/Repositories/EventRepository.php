<?php

namespace App\Repositories;

use App\Models\Event;
use App\Support\UnityContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class EventRepository extends BaseRepository
{

    public function __construct(UnityContext $unityContext)
    {
        parent::__construct(Event::class, $unityContext);
    }

    public function consolidate(int $id): Event
    {
        $event = $this->findOrFail($id);

        $event->consolidated = true;
        $event->save();

        return $event;
    }

    public function listByMonth(int $year, int $month): Collection
    {
        $query = Event::with(['entries.asset', 'header.asset', 'header.destinationAsset'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('consolidated', 'asc')
            ->orderByRaw('CASE WHEN due_day IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_day', 'asc');

        return $this->scopeToUnity($query)->get();
    }
}
