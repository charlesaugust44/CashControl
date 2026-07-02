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
    private EventService $eventService;

    public function __construct()
    {
        $this->entryRepository = new EntryRepository();
        $this->eventRepository = new EventRepository();
        $this->eventService = new EventService();
    }

    public function getActualBalance(Asset $asset): float
    {
        return (float) $asset->balance;
    }

    public function getForecastBalance(Asset $asset, int $year, int $month): float
    {
        $actual = $this->getActualBalance($asset);

        $events = $this->eventService->listByMonth($year, $month);

        $unconsolidated = $events->filter(function ($event) {
                if ($event->isComposite()) {
                    return !$event->consolidated || !$event->transfer_consolidated;
                }
                return !$event->consolidated;
            })
            ->sum(function ($event) use ($asset) {
                if ($event->isComposite()) {
                    $transferIndices = $event->getTransferEntryIndices();
                    $incomeExpenseIndices = $event->getIncomeExpenseEntryIndices();
                    $entries = $event->entries->values();

                    return $entries->reduce(function ($sum, $entry, $index) use ($asset, $event, $transferIndices, $incomeExpenseIndices) {
                        if ($entry->asset_id !== $asset->id) {
                            return $sum;
                        }

                        if (in_array($index, $transferIndices) && !$event->transfer_consolidated) {
                            return $sum + (float) $entry->amount;
                        }
                        if (in_array($index, $incomeExpenseIndices) && !$event->consolidated) {
                            return $sum + (float) $entry->amount;
                        }
                        return $sum;
                    }, 0);
                }

                return $event->entries->filter(fn($entry) => $entry->asset_id === $asset->id)
                    ->sum(fn($entry) => (float) $entry->amount);
            });

        return $actual + (float) $unconsolidated;
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
        $events = $this->eventService->listByMonth($year, $month);

        $income = $events->filter(function($e) {
                $type = $e->type?->value;
                return $type === 'income' || $type === 'income_with_transfer';
            })
            ->sum(function($e) {
                if ($e->type?->value === 'income_with_transfer') {
                    return max(0, (float) $e->entries[0]->amount);
                }
                return $e->entries->sum(fn($entry) => max(0, (float) $entry->amount));
            });

        $expense = $events->filter(function($e) {
                $type = $e->type?->value;
                return $type === 'expense' || $type === 'expense_with_transfer';
            })
            ->sum(function($e) {
                if ($e->type?->value === 'expense_with_transfer') {
                    return abs((float) $e->entries[2]->amount);
                }
                return $e->entries->sum(fn($entry) => abs((float) $entry->amount));
            });

        return ['income' => $income, 'expense' => $expense];
    }

    public function getMonthlyTotalsSplit(int $year, int $month): array
    {
        $events = $this->eventService->listByMonth($year, $month);

        $consolidatedIncome = 0;
        $consolidatedExpense = 0;
        $unconsolidatedIncome = 0;
        $unconsolidatedExpense = 0;

        foreach ($events as $event) {
            if ($event->type?->value === 'income') {
                if ($event->consolidated) {
                    $consolidatedIncome += $event->entries->sum(fn($e) => max(0, (float) $e->amount));
                } else {
                    $unconsolidatedIncome += $event->entries->sum(fn($e) => max(0, (float) $e->amount));
                }
            } elseif ($event->type?->value === 'expense') {
                if ($event->consolidated) {
                    $consolidatedExpense += $event->entries->sum(fn($e) => abs((float) $e->amount));
                } else {
                    $unconsolidatedExpense += $event->entries->sum(fn($e) => abs((float) $e->amount));
                }
            } elseif ($event->isIncomeWithTransfer()) {
                $entries = $event->entries->values();
                $incomeAmount = isset($entries[0]) ? max(0, (float) $entries[0]->amount) : 0;

                if ($event->consolidated) {
                    $consolidatedIncome += $incomeAmount;
                } else {
                    $unconsolidatedIncome += $incomeAmount;
                }
            } elseif ($event->isExpenseWithTransfer()) {
                $entries = $event->entries->values();
                $expenseAmount = isset($entries[2]) ? abs((float) $entries[2]->amount) : 0;

                if ($event->consolidated) {
                    $consolidatedExpense += $expenseAmount;
                } else {
                    $unconsolidatedExpense += $expenseAmount;
                }
            }
        }

        return [
            'consolidated' => ['income' => $consolidatedIncome, 'expense' => $consolidatedExpense],
            'unconsolidated' => ['income' => $unconsolidatedIncome, 'expense' => $unconsolidatedExpense],
        ];
    }

    public function getMonthlyBreakdown(int $months = 6, ?\Carbon\Carbon $referenceDate = null): array
    {
        $referenceDate = $referenceDate ?? now();
        $data = ['labels' => [], 'income' => [], 'expense' => []];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $referenceDate->copy()->subMonths($i);
            $totals = $this->getMonthlyTotals($date->year, $date->month);
            $data['labels'][] = $date->translatedFormat('M Y');
            $data['income'][] = round($totals['income'], 2);
            $data['expense'][] = round($totals['expense'], 2);
        }

        return $data;
    }

    public function getBalanceHistoryAggregated(int $months = 6, ?\Carbon\Carbon $referenceDate = null): array
    {
        $referenceDate = $referenceDate ?? now();
        $data = ['labels' => [], 'balances' => []];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $referenceDate->copy()->subMonths($i);
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

    public function getPendingConsolidations(int $year, int $month): Collection
    {
        $eventGenerationService = new EventGenerationService();
        $virtualEvents = $eventGenerationService->generateVirtualEvents($year, $month);
        $persistedEvents = \App\Models\Event::with(['header', 'entries.asset'])
            ->where(function ($query) {
                $query->where('consolidated', false)
                    ->orWhere('transfer_consolidated', false);
            })
            ->orderBy('date', 'asc')
            ->get();

        $merged = $virtualEvents->keyBy(fn ($event) => 'v_' . $event->header_id . '_' . $event->date->format('Y-m-d'));

        foreach ($persistedEvents as $persistedEvent) {
            $virtualKey = 'v_' . $persistedEvent->header_id . '_' . $persistedEvent->date->format('Y-m-d');
            unset($merged[$virtualKey]);

            $key = 'p_' . $persistedEvent->id;
            $merged[$key] = $persistedEvent;
        }

        return $merged->values()->sortBy([
            fn ($a, $b) => ($a->due_day === null ? 1 : 0) <=> ($b->due_day === null ? 1 : 0),
            fn ($a, $b) => ($a->due_day ?? PHP_INT_MAX) <=> ($b->due_day ?? PHP_INT_MAX),
        ])->values();
    }
}
