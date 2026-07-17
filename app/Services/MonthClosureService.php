<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Event;
use App\Repositories\AssetRepository;
use App\Repositories\EventRepository;
use App\Support\UnityContext;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MonthClosureService
{
    private EventRepository $eventRepository;
    private AssetRepository $assetRepository;
    private ?ConsolidationService $consolidationService = null;
    private UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->eventRepository = new EventRepository($unityContext);
        $this->assetRepository = new AssetRepository($unityContext);
    }

    private function getConsolidationService(): ConsolidationService
    {
        if ($this->consolidationService === null) {
            $this->consolidationService = new ConsolidationService($this->unityContext);
        }
        return $this->consolidationService;
    }

    public function closeMonth(int $year, int $month): void
    {
        $this->validateCloseMonth($year, $month);

        $closeDate = Carbon::create($year, $month, 1);

        $assets = $this->getScopedAssets();
        foreach ($assets as $asset) {
            $asset->closed_up_to = $closeDate;
            $asset->save();
        }

        $this->clearAllCache();
    }

    public function reopenLastMonth(): void
    {
        $lastClosedMonth = $this->getLastClosedMonth();

        if (!$lastClosedMonth) {
            throw new Exception('No month is closed');
        }

        $previousMonth = $lastClosedMonth->copy()->subMonth();

        $assets = $this->getScopedAssets();
        foreach ($assets as $asset) {
            $asset->closed_up_to = $previousMonth;
            $asset->save();
        }

        $eventsQuery = Event::whereYear('date', $lastClosedMonth->year)
            ->whereMonth('date', $lastClosedMonth->month)
            ->where('consolidated', true);

        if ($this->unityContext->has()) {
            $eventsQuery->where('unity_id', $this->unityContext->id());
        }

        $events = $eventsQuery->get();

        foreach ($events as $event) {
            $this->getConsolidationService()->unconsolidateEvent($event->id);
        }

        $this->clearAllCache();
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
        $assets = $this->getScopedAssets();

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

        $query = Event::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotIn('type', ['expense_with_transfer', 'income_with_transfer'])
                      ->where('consolidated', false);
                })->orWhere(function ($q) {
                    $q->whereIn('type', ['expense_with_transfer', 'income_with_transfer'])
                      ->where(function ($sub) {
                          $sub->where('consolidated', false)
                              ->orWhere('transfer_consolidated', false);
                      });
                });
            });

        if ($this->unityContext->has()) {
            $query->where('unity_id', $this->unityContext->id());
        }

        $unconsolidatedEvents = $query->count();

        return $unconsolidatedEvents === 0;
    }

    private function validateCloseMonth(int $year, int $month): void
    {
        if (!$this->canCloseMonth($year, $month)) {
            throw new Exception('Cannot close this month');
        }
    }

    private function getScopedAssets(): Collection
    {
        $query = Asset::query();

        if ($this->unityContext->has()) {
            $query->where('unity_id', $this->unityContext->id());
        }

        return $query->get();
    }

    private function clearAllCache(): void
    {
        Cache::tags(['events', 'forecast'])->flush();
        BalanceService::clearCache();
    }
}
