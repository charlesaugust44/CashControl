<?php

namespace App\Services;

use App\Models\Asset;
use App\Repositories\EntryRepository;
use App\Repositories\EventRepository;
use Illuminate\Support\Collection;

class BalanceService
{
    private EntryRepository $entryRepository;
    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->entryRepository = new EntryRepository();
        $this->eventRepository = new EventRepository();
    }

    public function getActualBalance(Asset $asset): float
    {
        return (float) $asset->entries()
            ->whereHas('event', function ($query) {
                $query->where('consolidated', true);
            })
            ->sum('amount');
    }

    public function getForecastBalance(Asset $asset, int $year, int $month): float
    {
        $actual = $this->getActualBalance($asset);

        $unconsolidated = (float) $asset->entries()
            ->whereHas('event', function ($query) use ($year, $month) {
                $query->where('consolidated', false)
                    ->whereYear('date', '<=', $year)
                    ->whereMonth('date', '<=', $month);
            })
            ->sum('amount');

        return $actual + $unconsolidated;
    }

    public function getBalanceHistory(Asset $asset): Collection
    {
        return $asset->entries()
            ->with('event')
            ->whereHas('event', function ($query) {
                $query->where('consolidated', true);
            })
            ->get()
            ->groupBy(fn($entry) => $entry->event->date->format('Y-m'))
            ->map(function ($entries) {
                return $entries->sum('amount');
            });
    }

    public function getMonthSummary(int $year, int $month): Collection
    {
        $assets = Asset::all();

        return $assets->map(function ($asset) use ($year, $month) {
            return [
                'asset' => $asset,
                'actual_balance' => $this->getActualBalance($asset),
                'forecast_balance' => $this->getForecastBalance($asset, $year, $month),
            ];
        });
    }
}
