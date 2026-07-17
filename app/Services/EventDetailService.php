<?php

namespace App\Services;

use App\Enums\EventType;
use App\Models\Asset;
use App\Models\Entry;
use App\Models\Event;
use App\Repositories\AssetRepository;
use App\Repositories\EventRepository;
use App\Repositories\HeaderRepository;
use App\Support\UnityContext;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EventDetailService
{
    private EventRepository $eventRepository;

    private HeaderRepository $headerRepository;

    private AssetRepository $assetRepository;

    private MonthClosureService $monthClosureService;

    private ConsolidationService $consolidationService;

    private UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->eventRepository = new EventRepository($unityContext);
        $this->headerRepository = new HeaderRepository($unityContext);
        $this->assetRepository = new AssetRepository($unityContext);
        $this->monthClosureService = new MonthClosureService($unityContext);
        $this->consolidationService = new ConsolidationService($unityContext);
    }

    public function getEvent(int $id): ?Event
    {
        return $this->eventRepository->findOrFail($id);
    }

    public function getVirtualEvent(int $headerId, int $year, int $month): ?object
    {
        $eventGenerationService = new EventGenerationService($this->unityContext);
        $virtualEvents = $eventGenerationService->generateVirtualEvents($year, $month);

        return $virtualEvents->first(fn ($e) => $e->header_id === $headerId);
    }

    public function persistVirtualEvent(int $headerId, int $year, int $month, array $entriesData): Event
    {
        $header = $this->headerRepository->findOrFail($headerId);
        $eventDate = Carbon::create($year, $month, 1);

        $event = DB::transaction(function () use ($header, $eventDate, $entriesData) {
            $eventData = [
                'header_id' => $header->id,
                'type' => $header->type,
                'name' => $header->name,
                'date' => $eventDate,
                'due_day' => $entriesData['due_day'] ?? $header->due_day,
                'consolidated' => false,
                'transfer_consolidated' => false,
                'note' => $entriesData['note'] ?? null,
            ];

            if ($this->unityContext->has()) {
                $eventData['unity_id'] = $this->unityContext->id();
            }

            $event = Event::create($eventData);

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

        $this->clearRelatedCache($year, $month);

        return $event;
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

        $event = DB::transaction(function () use ($data, $eventDate) {
            $eventData = [
                'header_id' => null,
                'type' => EventType::from($data['type']),
                'name' => $data['name'],
                'date' => $eventDate,
                'due_day' => $data['due_day'] ?? null,
                'consolidated' => false,
                'transfer_consolidated' => false,
                'note' => $data['note'] ?? null,
            ];

            if ($this->unityContext->has()) {
                $eventData['unity_id'] = $this->unityContext->id();
            }

            $event = Event::create($eventData);

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

        $this->clearRelatedCache($eventDate->year, $eventDate->month);

        return $event;
    }

    public function updateEvent(int $id, array $data): Event
    {
        $event = $this->eventRepository->findOrFail($id);

        $this->validateEventEditable($event);

        $event = DB::transaction(function () use ($event, $data) {
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

        $eventDate = Carbon::parse($event->date);
        $this->clearRelatedCache($eventDate->year, $eventDate->month);

        return $event;
    }

    private function updatePartiallyConsolidatedEntries(Event $event, array $data): void
    {
        $positions = $data['positions'] ?? [];

        if ($event->consolidated && ! $event->transfer_consolidated) {
            $indicesToUpdate = $event->getTransferEntryIndices();
        } elseif (! $event->consolidated && $event->transfer_consolidated) {
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
            if ($formIndex === false || ! isset($data['entries'][$formIndex])) {
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

        $eventDate = Carbon::parse($event->date);

        if ($event->consolidated) {
            $this->consolidationService->unconsolidateEvent($event->id);
        }

        $event->entries()->delete();
        $event->delete();

        $this->clearRelatedCache($eventDate->year, $eventDate->month);
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

    public function getAssets(): Collection
    {
        $query = Asset::orderBy('name');

        if ($this->unityContext->has()) {
            $query->where('unity_id', $this->unityContext->id());
        }

        return $query->get();
    }

    private function adjustAmountSign(EventType $type, float $amount, int $entryIndex): float
    {
        return $type->entrySign($entryIndex) * abs($amount);
    }

    private function clearRelatedCache(int $year, int $month): void
    {
        $unityId = $this->unityContext->has() ? $this->unityContext->id() : 'global';
        Cache::tags(['events'])->forget("unity_{$unityId}:events:{$year}-{$month}");
        Cache::tags(['forecast'])->flush();
        BalanceService::clearCache();
    }
}
