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

    public function getTotalBalance(): float
    {
        return Asset::all()->sum(fn($asset) => $this->getActualBalance($asset));
    }

    public function getMonthlyTotals(int $year, int $month): array
    {
        $events = $this->eventRepository->listByMonth($year, $month);

        $income = $events->filter(fn($e) => $e->header->type?->value === 'income')
            ->flatMap(fn($e) => $e->entries)
            ->sum(fn($entry) => max(0, (float) $entry->amount));

        $expense = $events->filter(fn($e) => $e->header->type?->value === 'expense')
            ->flatMap(fn($e) => $e->entries)
            ->sum(fn($entry) => abs((float) $entry->amount));

        return ['income' => $income, 'expense' => $expense];
    }

    public function getMonthlyBreakdown(int $months = 6): array
    {
        $data = ['labels' => [], 'income' => [], 'expense' => []];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $totals = $this->getMonthlyTotals($date->year, $date->month);
            $data['labels'][] = $date->translatedFormat('M Y');
            $data['income'][] = round($totals['income'], 2);
            $data['expense'][] = round($totals['expense'], 2);
        }

        return $data;
    }

    public function getBalanceHistoryAggregated(int $months = 6): array
    {
        $data = ['labels' => [], 'balances' => []];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $label = $date->translatedFormat('M Y');
            $data['labels'][] = $label;

            $total = 0;
            foreach (Asset::all() as $asset) {
                $history = $this->getBalanceHistory($asset);
                $key = $date->format('Y-m');
                $total += (float) ($history[$key] ?? 0);
            }
            $data['balances'][] = round($total, 2);
        }

        return $data;
    }

    public function getUnusualIncreases(float $threshold = 10.0): Collection
    {
        $headers = \App\Models\Header::with('events.entries')->get();
        $alerts = collect();

        foreach ($headers as $header) {
            $events = $header->events()
                ->with('entries')
                ->orderBy('date', 'desc')
                ->limit(2)
                ->get();

            if ($events->count() < 2) {
                continue;
            }

            $current = $events->first();
            $previous = $events->last();

            $currentAmount = abs($current->entries->sum('amount'));
            $previousAmount = abs($previous->entries->sum('amount'));

            if ($previousAmount <= 0 || $currentAmount <= $previousAmount) {
                continue;
            }

            $percentIncrease = round((($currentAmount - $previousAmount) / $previousAmount) * 100, 1);

            if ($percentIncrease >= $threshold) {
                $alerts->push([
                    'header_id' => $header->id,
                    'header_name' => $header->name,
                    'event_id' => $current->id,
                    'current_amount' => $currentAmount,
                    'previous_amount' => $previousAmount,
                    'percent' => $percentIncrease,
                    'date' => $current->date,
                ]);
            }
        }

        return $alerts;
    }

    public function getPendingConsolidations(): Collection
    {
        return \App\Models\Event::with(['header', 'entries.asset'])
            ->where('consolidated', false)
            ->orderBy('date', 'asc')
            ->get();
    }
}
