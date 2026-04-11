<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Collection;

class EntryRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(Entry::class);
    }

    public function history(Asset $asset): Collection
    {
        return Entry::query()
            ->select('entries.*', 'events.note as event_note', 'events.date as event_date')
            ->join('events', 'events.id', '=', 'entries.event_id')
            ->where('asset_id', $asset->id)
            ->orderBy('events.date')
            ->get();
    }
}
