<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Entry;
use App\Models\Event;
use App\Support\UnityContext;
use Illuminate\Database\Eloquent\Collection;

class EntryRepository extends BaseRepository
{

    public function __construct(UnityContext $unityContext)
    {
        parent::__construct(Entry::class, $unityContext);
    }

    public function history(Asset $asset): Collection
    {
        $query = Event::query()
            ->with(['header', 'entries.asset'])
            ->whereHas('entries', function ($query) use ($asset) {
                $query->where('asset_id', $asset->id);
            })
            ->orderBy('date', 'desc');

        return $this->scopeToUnity($query)->get();
    }

    public function historyByMonth(Asset $asset, int $year, int $month): Collection
    {
        $query = Event::query()
            ->with(['header', 'entries.asset'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereHas('entries', function ($query) use ($asset) {
                $query->where('asset_id', $asset->id);
            })
            ->orderBy('date', 'desc');

        return $this->scopeToUnity($query)->get();
    }
}
