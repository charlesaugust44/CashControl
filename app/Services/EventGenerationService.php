<?php

namespace App\Services;

use App\Enums\EventRule;
use App\Enums\EventType;
use App\Models\Asset;
use App\Models\Entry;
use App\Models\Event;
use App\Models\Header;
use App\Repositories\EntryRepository;
use App\Repositories\EventRepository;
use App\Repositories\HeaderRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;

class EventGenerationService
{
    private HeaderRepository $headerRepository;

    private EventRepository $eventRepository;

    private EntryRepository $entryRepository;

    private MonthClosureService $monthClosureService;

    public function __construct()
    {
        $this->headerRepository = new HeaderRepository;
        $this->eventRepository = new EventRepository;
        $this->entryRepository = new EntryRepository;
        $this->monthClosureService = new MonthClosureService;
    }

    public function getMonthEvents(int $year, int $month): Enumerable
    {
        if ($this->monthClosureService->isMonthClosed($year, $month)) {
            return $this->eventRepository->listByMonth($year, $month);
        }

        $virtualEvents = $this->generateVirtualEvents($year, $month);
        $persistedEvents = $this->eventRepository->listByMonth($year, $month);

        return $this->mergeEvents($virtualEvents, $persistedEvents);
    }

    public function generateVirtualEvents(int $year, int $month): Collection
    {
        $activeHeaders = $this->headerRepository->active($year, $month);
        $virtualEvents = collect();

        foreach ($activeHeaders as $header) {
            $event = $this->createVirtualEvent($header, $year, $month);
            if ($event) {
                $virtualEvents->push($event);
            }
        }

        return $virtualEvents;
    }

    private function createVirtualEvent(Header $header, int $year, int $month): ?object
    {
        $eventDate = Carbon::create($year, $month, 1);

        if ($header->start_date && $eventDate->lessThan($header->start_date->copy()->startOfMonth())) {
            return null;
        }

        if ($header->end_date && $eventDate->greaterThan($header->end_date->copy()->endOfMonth())) {
            return null;
        }

        $amount = $this->applyRule($header, $year, $month);

        $event = new Event([
            'header_id' => $header->id,
            'type' => $header->type,
            'name' => $header->name,
            'date' => $eventDate,
            'consolidated' => false,
            'note' => null,
        ]);
        $event->id = 0;
        $event->header = $header;

        $entries = $this->createVirtualEntries($header, $amount);
        $event->entries = $entries;

        return $event;
    }

    private function createVirtualEntries(Header $header, float $amount): Collection
    {
        $entries = collect();

        if ($header->isTransfer()) {
            if ($header->asset_id) {
                $debitEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->asset_id,
                    'amount' => -$amount,
                ]);
                $debitEntry->asset = Asset::find($header->asset_id);
                $entries->push($debitEntry);
            }

            if ($header->destination_asset_id) {
                $creditEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->destination_asset_id,
                    'amount' => $amount,
                ]);
                $creditEntry->asset = Asset::find($header->destination_asset_id);
                $entries->push($creditEntry);
            }
        } elseif ($header->isExpenseWithTransfer()) {
            if ($header->asset_id) {
                $transferOutEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->asset_id,
                    'amount' => -$amount,
                ]);
                $transferOutEntry->asset = Asset::find($header->asset_id);
                $entries->push($transferOutEntry);
            }

            if ($header->destination_asset_id) {
                $transferInEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->destination_asset_id,
                    'amount' => $amount,
                ]);
                $transferInEntry->asset = Asset::find($header->destination_asset_id);
                $entries->push($transferInEntry);

                $expenseEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->destination_asset_id,
                    'amount' => -$amount,
                ]);
                $expenseEntry->asset = Asset::find($header->destination_asset_id);
                $entries->push($expenseEntry);
            }
        } elseif ($header->isIncomeWithTransfer()) {
            if ($header->asset_id) {
                $incomeEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->asset_id,
                    'amount' => $amount,
                ]);
                $incomeEntry->asset = Asset::find($header->asset_id);
                $entries->push($incomeEntry);

                $transferOutEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->asset_id,
                    'amount' => -$amount,
                ]);
                $transferOutEntry->asset = Asset::find($header->asset_id);
                $entries->push($transferOutEntry);
            }

            if ($header->destination_asset_id) {
                $transferInEntry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->destination_asset_id,
                    'amount' => $amount,
                ]);
                $transferInEntry->asset = Asset::find($header->destination_asset_id);
                $entries->push($transferInEntry);
            }
        } else {
            if ($header->asset_id) {
                $entryAmount = $header->type === EventType::Expense ? -$amount : $amount;
                $entry = new Entry([
                    'event_id' => 0,
                    'asset_id' => $header->asset_id,
                    'amount' => $entryAmount,
                ]);
                $entry->asset = Asset::find($header->asset_id);
                $entries->push($entry);
            }
        }

        return $entries;
    }

    private function applyRule(Header $header, int $year, int $month): float
    {
        return match ($header->rule) {
            EventRule::Fixed => (float) $header->default_amount,
            EventRule::MaxLastFiveMonths => $this->calculateMaxLastFiveMonths($header, $year, $month),
            EventRule::MeanLastFiveMonths => $this->calculateMeanLastFiveMonths($header, $year, $month),
            default => (float) $header->default_amount,
        };
    }

    private function calculateMaxLastFiveMonths(Header $header, int $year, int $month): float
    {
        $amounts = $this->getLastFiveMonthsAmounts($header, $year, $month);

        if ($amounts->isEmpty()) {
            return (float) $header->default_amount;
        }

        return $amounts->max();
    }

    private function calculateMeanLastFiveMonths(Header $header, int $year, int $month): float
    {
        $amounts = $this->getLastFiveMonthsAmounts($header, $year, $month);

        if ($amounts->isEmpty()) {
            return (float) $header->default_amount;
        }

        return $amounts->avg();
    }

    private function getLastFiveMonthsAmounts(Header $header, int $year, int $month): Collection
    {
        $amounts = collect();
        $checkDate = Carbon::create($year, $month, 1);

        for ($i = 1; $i <= 5; $i++) {
            $checkDate->subMonth();

            $persistedEvent = Event::where('header_id', $header->id)
                ->whereYear('date', $checkDate->year)
                ->whereMonth('date', $checkDate->month)
                ->with('entries')
                ->first();

            if ($persistedEvent) {
                $totalAmount = $persistedEvent->entries->sum('amount');
                if ($header->isTransfer()) {
                    $totalAmount = $persistedEvent->entries->where('amount', '>', 0)->sum('amount');
                } elseif ($header->isExpenseWithTransfer()) {
                    $totalAmount = abs($persistedEvent->entries->where('amount', '<', 0)->sum('amount')) / 2;
                } elseif ($header->isIncomeWithTransfer()) {
                    $totalAmount = $persistedEvent->entries->where('amount', '>', 0)->sum('amount');
                } else {
                    $totalAmount = abs($totalAmount);
                }
                $amounts->push($totalAmount);
            } else {
                $forecastedAmount = $this->calculateForecastedAmount($header, $checkDate->year, $checkDate->month);
                if ($forecastedAmount !== null) {
                    $amounts->push($forecastedAmount);
                }
            }
        }

        return $amounts;
    }

    private function calculateForecastedAmount(Header $header, int $year, int $month): ?float
    {
        $eventDate = Carbon::create($year, $month, 1);

        if ($header->start_date && $eventDate->lessThan($header->start_date->copy()->startOfMonth())) {
            return null;
        }

        if ($header->end_date && $eventDate->greaterThan($header->end_date->copy()->endOfMonth())) {
            return null;
        }

        return match ($header->rule) {
            EventRule::Fixed => (float) $header->default_amount,
            EventRule::MaxLastFiveMonths => $this->calculateMaxLastFiveMonths($header, $year, $month),
            EventRule::MeanLastFiveMonths => $this->calculateMeanLastFiveMonths($header, $year, $month),
            default => (float) $header->default_amount,
        };
    }

    private function mergeEvents(Collection $virtualEvents, Collection $persistedEvents): Enumerable
    {
        $merged = $virtualEvents->keyBy(fn ($event) => 'v_'.$event->header_id.'_'.$event->date->format('Y-m-d'));

        foreach ($persistedEvents as $persistedEvent) {
            $virtualKey = 'v_'.$persistedEvent->header_id.'_'.$persistedEvent->date->format('Y-m-d');
            unset($merged[$virtualKey]);

            $key = 'p_'.$persistedEvent->id;
            $merged[$key] = $persistedEvent;
        }

        return $merged->values();
    }
}
