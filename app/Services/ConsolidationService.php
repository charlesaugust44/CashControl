<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Event;
use App\Repositories\AssetRepository;
use App\Repositories\EventRepository;
use Carbon\Carbon;
use Exception;

class ConsolidationService
{
    private EventRepository $eventRepository;
    private AssetRepository $assetRepository;
    private MonthClosureService $monthClosureService;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $this->assetRepository = new AssetRepository();
        $this->monthClosureService = new MonthClosureService();
    }

    public function consolidateEvent(int $eventId): Event
    {
        $event = $this->eventRepository->findOrFail($eventId);

        $this->validateConsolidation($event);

        $event->consolidated = true;
        $event->save();

        $this->updateBalanceOnConsolidate($event);

        return $event->fresh(['entries.asset', 'header']);
    }

    public function unconsolidateEvent(int $eventId): Event
    {
        $event = $this->eventRepository->findOrFail($eventId);

        $this->validateUnconsolidation($event);

        $this->rollbackBalanceOnUnconsolidate($event);

        $event->consolidated = false;
        $event->save();

        return $event->fresh(['entries.asset', 'header']);
    }

    private function validateConsolidation(Event $event): void
    {
        $eventMonth = Carbon::parse($event->date);
        $currentMonth = Carbon::now()->startOfMonth();

        if ($eventMonth->greaterThan($currentMonth)) {
            throw new Exception('Cannot consolidate future events');
        }

        if ($this->monthClosureService->isMonthClosed($eventMonth->year, $eventMonth->month)) {
            throw new Exception('Cannot consolidate events in closed months');
        }

        if ($event->consolidated) {
            throw new Exception('Event is already consolidated');
        }
    }

    private function validateUnconsolidation(Event $event): void
    {
        if (!$event->consolidated) {
            throw new Exception('Event is not consolidated');
        }

        $eventMonth = Carbon::parse($event->date);

        if ($this->monthClosureService->isMonthClosed($eventMonth->year, $eventMonth->month)) {
            throw new Exception('Cannot unconsolidate events in closed months');
        }
    }

    private function updateBalanceOnConsolidate(Event $event): void
    {
        foreach ($event->entries as $entry) {
            $asset = $this->assetRepository->findOrFail($entry->asset_id);
            $asset->balance = (float) $asset->balance + (float) $entry->amount;
            $asset->save();
        }
    }

    private function rollbackBalanceOnUnconsolidate(Event $event): void
    {
        foreach ($event->entries as $entry) {
            $asset = $this->assetRepository->findOrFail($entry->asset_id);
            $asset->balance = (float) $asset->balance - (float) $entry->amount;
            $asset->save();
        }
    }
}
