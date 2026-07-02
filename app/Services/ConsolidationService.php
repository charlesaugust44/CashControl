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

    public function consolidateExpenseIncome(int $eventId): Event
    {
        $event = $this->eventRepository->findOrFail($eventId);

        if (!$event->isComposite()) {
            throw new Exception('This method is only for composite events');
        }

        $this->validateConsolidation($event);

        $event->consolidated = true;
        $event->save();

        $this->updateBalanceForEntries($event, $event->getIncomeExpenseEntryIndices());

        return $event->fresh(['entries.asset', 'header']);
    }

    public function consolidateTransfer(int $eventId): Event
    {
        $event = $this->eventRepository->findOrFail($eventId);

        if (!$event->isComposite()) {
            throw new Exception('This method is only for composite events');
        }

        $this->validateTransferConsolidation($event);

        $event->transfer_consolidated = true;
        $event->save();

        $this->updateBalanceForEntries($event, $event->getTransferEntryIndices());

        return $event->fresh(['entries.asset', 'header']);
    }

    public function unconsolidateEvent(int $eventId): Event
    {
        $event = $this->eventRepository->findOrFail($eventId);

        $this->validateUnconsolidation($event);

        if ($event->isComposite()) {
            if ($event->consolidated) {
                $this->rollbackBalanceForEntries($event, $event->getIncomeExpenseEntryIndices());
            }
            if ($event->transfer_consolidated) {
                $this->rollbackBalanceForEntries($event, $event->getTransferEntryIndices());
            }
            $event->consolidated = false;
            $event->transfer_consolidated = false;
        } else {
            $this->rollbackBalanceOnUnconsolidate($event);
            $event->consolidated = false;
        }
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

    private function validateTransferConsolidation(Event $event): void
    {
        $eventMonth = Carbon::parse($event->date);
        $currentMonth = Carbon::now()->startOfMonth();

        if ($eventMonth->greaterThan($currentMonth)) {
            throw new Exception('Cannot consolidate future events');
        }

        if ($this->monthClosureService->isMonthClosed($eventMonth->year, $eventMonth->month)) {
            throw new Exception('Cannot consolidate events in closed months');
        }

        if ($event->transfer_consolidated) {
            throw new Exception('Transfer is already consolidated');
        }
    }

    private function validateUnconsolidation(Event $event): void
    {
        if ($event->isComposite()) {
            if (!$event->consolidated && !$event->transfer_consolidated) {
                throw new Exception('Event is not consolidated');
            }
        } else {
            if (!$event->consolidated) {
                throw new Exception('Event is not consolidated');
            }
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

    private function updateBalanceForEntries(Event $event, array $indices): void
    {
        $entries = $event->entries->values();

        foreach ($indices as $index) {
            if (!isset($entries[$index])) {
                continue;
            }

            $entry = $entries[$index];
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

    private function rollbackBalanceForEntries(Event $event, array $indices): void
    {
        $entries = $event->entries->values();

        foreach ($indices as $index) {
            if (!isset($entries[$index])) {
                continue;
            }

            $entry = $entries[$index];
            $asset = $this->assetRepository->findOrFail($entry->asset_id);
            $asset->balance = (float) $asset->balance - (float) $entry->amount;
            $asset->save();
        }
    }
}
