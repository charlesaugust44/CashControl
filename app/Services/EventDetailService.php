<?php

namespace App\Services;

use App\Enums\EventType;
use App\Models\Asset;
use App\Models\Entry;
use App\Models\Event;
use App\Models\Header;
use App\Repositories\AssetRepository;
use App\Repositories\EventRepository;
use App\Repositories\HeaderRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class EventDetailService
{
    private EventRepository $eventRepository;
    private HeaderRepository $headerRepository;
    private AssetRepository $assetRepository;
    private MonthClosureService $monthClosureService;
    private ConsolidationService $consolidationService;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $this->headerRepository = new HeaderRepository();
        $this->assetRepository = new AssetRepository();
        $this->monthClosureService = new MonthClosureService();
        $this->consolidationService = new ConsolidationService();
    }

    public function getEvent(int $id): ?Event
    {
        return $this->eventRepository->findOrFail($id);
    }

    public function getVirtualEvent(int $headerId, int $year, int $month): ?object
    {
        $eventGenerationService = new EventGenerationService();
        $virtualEvents = $eventGenerationService->generateVirtualEvents($year, $month);

        return $virtualEvents->first(fn($e) => $e->header_id === $headerId);
    }

    public function persistVirtualEvent(int $headerId, int $year, int $month, array $entriesData): Event
    {
        $header = $this->headerRepository->findOrFail($headerId);
        $eventDate = Carbon::create($year, $month, 1);

        return DB::transaction(function () use ($header, $eventDate, $entriesData) {
            $event = Event::create([
                'header_id' => $header->id,
                'type' => $header->type,
                'name' => $header->name,
                'date' => $eventDate,
                'due_day' => $entriesData['due_day'] ?? $header->due_day,
                'consolidated' => false,
                'transfer_consolidated' => false,
                'note' => $entriesData['note'] ?? null,
            ]);

            foreach ($entriesData['entries'] as $index => $entryData) {
                $amount = $this->adjustAmountSign($header->type, $entryData['amount'], $index);
                Entry::create([
                    'event_id' => $event->id,
                    'asset_id' => $entryData['asset_id'],
                    'amount' => $amount,
                ]);
            }

            return $event->load(['entries.asset', 'header']);
        });
    }

    public function createStandaloneEvent(array $data): Event
    {
        $eventDate = Carbon::parse($data['date']);

        if ($this->monthClosureService->isMonthClosed($eventDate->year, $eventDate->month)) {
            throw new Exception('Cannot create events in closed months');
        }

        if ($eventDate->greaterThan(Carbon::now()->endOfMonth())) {
            throw new Exception('Cannot create future events');
        }

        return DB::transaction(function () use ($data, $eventDate) {
            $event = Event::create([
                'header_id' => null,
                'type' => EventType::from($data['type']),
                'name' => $data['name'],
                'date' => $eventDate,
                'due_day' => $data['due_day'] ?? null,
                'consolidated' => false,
                'transfer_consolidated' => false,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['entries'] as $index => $entryData) {
                $amount = $this->adjustAmountSign($event->type, $entryData['amount'], $index);
                Entry::create([
                    'event_id' => $event->id,
                    'asset_id' => $entryData['asset_id'],
                    'amount' => $amount,
                ]);
            }

            return $event->load(['entries.asset']);
        });
    }

    public function updateEvent(int $id, array $data): Event
    {
        $event = $this->eventRepository->findOrFail($id);

        $this->validateEventEditable($event);

        return DB::transaction(function () use ($event, $data) {
            $needsUnconsolidate = $event->isFullyConsolidated();

            if ($needsUnconsolidate) {
                $this->consolidationService->unconsolidateEvent($event->id);
                $event->refresh();
            }

            $event->update([
                'due_day' => $data['due_day'] ?? $event->due_day,
                'note' => $data['note'] ?? null,
            ]);

            if ($event->isComposite() && $event->isPartiallyConsolidated()) {
                $this->updatePartiallyConsolidatedEntries($event, $data);
            } else {
                $event->entries()->delete();

                foreach ($data['entries'] as $index => $entryData) {
                    $amount = $this->adjustAmountSign($event->type, $entryData['amount'], $index);
                    Entry::create([
                        'event_id' => $event->id,
                        'asset_id' => $entryData['asset_id'],
                        'amount' => $amount,
                    ]);
                }
            }

            return $event->load(['entries.asset', 'header']);
        });
    }

    private function updatePartiallyConsolidatedEntries(Event $event, array $data): void
    {
        $positions = $data['positions'] ?? [];

        if ($event->consolidated && !$event->transfer_consolidated) {
            $indicesToUpdate = $event->getTransferEntryIndices();
        } elseif (!$event->consolidated && $event->transfer_consolidated) {
            $indicesToUpdate = $event->getIncomeExpenseEntryIndices();
        } else {
            $event->entries()->delete();
            foreach ($data['entries'] as $index => $entryData) {
                $amount = $this->adjustAmountSign($event->type, $entryData['amount'], $index);
                Entry::create([
                    'event_id' => $event->id,
                    'asset_id' => $entryData['asset_id'],
                    'amount' => $amount,
                ]);
            }
            return;
        }

        foreach ($indicesToUpdate as $canonicalIndex) {
            $formIndex = array_search($canonicalIndex, $positions);
            if ($formIndex === false || !isset($data['entries'][$formIndex])) {
                continue;
            }

            $entryData = $data['entries'][$formIndex];
            $amount = $this->adjustAmountSign($event->type, $entryData['amount'], $canonicalIndex);

            $existingEntry = $event->entries()
                ->where('asset_id', $entryData['asset_id'])
                ->first();

            if ($existingEntry && in_array($canonicalIndex, $indicesToUpdate)) {
                $existingEntry->update([
                    'asset_id' => $entryData['asset_id'],
                    'amount' => $amount,
                ]);
            } else {
                Entry::create([
                    'event_id' => $event->id,
                    'asset_id' => $entryData['asset_id'],
                    'amount' => $amount,
                ]);
            }
        }
    }

    public function deleteEvent(int $id): void
    {
        $event = $this->eventRepository->findOrFail($id);

        $this->validateEventEditable($event);

        if ($event->consolidated) {
            $this->consolidationService->unconsolidateEvent($event->id);
        }

        $event->entries()->delete();
        $event->delete();
    }

    public function validateEventEditable(Event $event): void
    {
        $eventDate = Carbon::parse($event->date);

        if ($this->monthClosureService->isMonthClosed($eventDate->year, $eventDate->month)) {
            throw new Exception('Cannot edit events in closed months');
        }

        if ($eventDate->greaterThan(Carbon::now()->endOfMonth())) {
            throw new Exception('Cannot edit future events');
        }
    }

    public function getAssets(): \Illuminate\Database\Eloquent\Collection
    {
        return Asset::orderBy('name')->get();
    }

    private function adjustAmountSign(EventType $type, float $amount, int $entryIndex): float
    {
        $absoluteAmount = abs($amount);

        if ($type === EventType::Expense) {
            return -$absoluteAmount;
        }

        if ($type === EventType::Transfer) {
            if ($entryIndex === 0) {
                return -$absoluteAmount;
            }
            return $absoluteAmount;
        }

        if ($type === EventType::ExpenseWithTransfer) {
            if ($entryIndex === 0) {
                return -$absoluteAmount;
            }
            if ($entryIndex === 1) {
                return $absoluteAmount;
            }
            return -$absoluteAmount;
        }

        if ($type === EventType::IncomeWithTransfer) {
            if ($entryIndex === 0) {
                return $absoluteAmount;
            }
            if ($entryIndex === 1) {
                return -$absoluteAmount;
            }
            return $absoluteAmount;
        }

        return $absoluteAmount;
    }
}
