<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Entry;
use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class EntryRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(Entry::class);
    }

    public function history(Asset $asset): Collection
    {
        return Event::query()
            ->with([
                'header',
                'entries' => function ($query) use ($asset) {
                    $query->where('asset_id', $asset->id)->with('asset');
                }
            ])
            ->whereHas('entries', function ($query) use ($asset) {
                $query->where('asset_id', $asset->id);
            })
            ->orderBy('date', 'desc')
            ->get();
    }
}
