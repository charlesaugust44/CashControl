<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Event;
use App\Repositories\AssetRepository;
use App\Repositories\EventRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class MonthClosureService
{
    private EventRepository $eventRepository;
    private AssetRepository $assetRepository;
    private ConsolidationService $consolidationService;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $this->assetRepository = new AssetRepository();
    }

    public function closeMonth(int $year, int $month): void
    {
        $this->validateCloseMonth($year, $month);

        $closeDate = Carbon::create($year, $month, 1);

        $assets = Asset::all();
        foreach ($assets as $asset) {
            $asset->closed_up_to = $closeDate;
            $asset->save();
        }
    }

    public function reopenLastMonth(): void
    {
        $lastClosedMonth = $this->getLastClosedMonth();

        if (!$lastClosedMonth) {
            throw new Exception('No month is closed');
        }

        $events = Event::whereYear('date', $lastClosedMonth->year)
            ->whereMonth('date', $lastClosedMonth->month)
            ->where('consolidated', true)
            ->get();

        foreach ($events as $event) {
            $this->consolidationService->unconsolidateEvent($event->id);
        }

        $assets = Asset::all();
        foreach ($assets as $asset) {
            $previousMonth = $lastClosedMonth->copy()->subMonth();
            $asset->closed_up_to = $previousMonth->month === 12 && $previousMonth->year < $lastClosedMonth->year
                ? null
                : $previousMonth;

            if ($this->isMonthClosed($asset->closed_up_to?->year, $asset->closed_up_to?->month)) {
                continue;
            }

            $asset->closed_up_to = null;
            $asset->save();
        }

        $this->resetAllClosedUpTo(null);
    }

    public function isMonthClosed(?int $year, ?int $month): bool
    {
        if ($year === null || $month === null) {
            return false;
        }

        $lastClosedMonth = $this->getLastClosedMonth();

        if (!$lastClosedMonth) {
            return false;
        }

        $checkDate = Carbon::create($year, $month, 1);

        return $checkDate->lessThanOrEqualTo($lastClosedMonth);
    }

    public function getLastClosedMonth(): ?Carbon
    {
        $assets = Asset::all();

        if ($assets->isEmpty()) {
            return null;
        }

        $closedDates = $assets->pluck('closed_up_to')->filter()->map(fn($date) => Carbon::parse($date));

        if ($closedDates->isEmpty()) {
            return null;
        }

        return $closedDates->min();
    }

    public function canCloseMonth(int $year, int $month): bool
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $checkDate = Carbon::create($year, $month, 1);

        if ($checkDate->greaterThan($currentMonth)) {
            return false;
        }

        if ($this->isMonthClosed($year, $month)) {
            return false;
        }

        $lastClosedMonth = $this->getLastClosedMonth();
        if ($lastClosedMonth) {
            $expectedMonth = $lastClosedMonth->copy()->addMonth();
            if (!$checkDate->equalTo($expectedMonth)) {
                return false;
            }
        }

        $unconsolidatedEvents = Event::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('consolidated', false)
            ->count();

        return $unconsolidatedEvents === 0;
    }

    private function validateCloseMonth(int $year, int $month): void
    {
        if (!$this->canCloseMonth($year, $month)) {
            throw new Exception('Cannot close this month');
        }
    }

    private function resetAllClosedUpTo(?Carbon $date): void
    {
        $assets = Asset::all();
        foreach ($assets as $asset) {
            $asset->closed_up_to = $date;
            $asset->save();
        }
    }
}
