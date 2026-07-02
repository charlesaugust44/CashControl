<?php

namespace App\Http\Controllers;

use App\Services\ConsolidationService;
use App\Services\EventService;
use App\Services\MonthClosureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{

    protected EventService $eventService;
    protected ConsolidationService $consolidationService;
    protected MonthClosureService $monthClosureService;

    public function __construct()
    {
        $this->eventService = new EventService;
        $this->consolidationService = new ConsolidationService;
        $this->monthClosureService = new MonthClosureService;
    }

    public function index(Request $request): View
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $monthDate = \Carbon\Carbon::parse($currentMonth);
        $filter = $request->get('filter', 'all');

        $events = $this->eventService->listByMonth(
            $monthDate->year,
            $monthDate->month
        );

        $totalIncome = $events->filter(function($e) {
                $type = $e->type?->value;
                return $type === 'income' || $type === 'income_with_transfer';
            })
            ->sum(function($e) {
                if ($e->type?->value === 'income_with_transfer') {
                    return max(0, (float) $e->entries[0]->amount);
                }
                return $e->entries->sum(fn($entry) => max(0, (float) $entry->amount));
            });

        $totalExpense = $events->filter(function($e) {
                $type = $e->type?->value;
                return $type === 'expense' || $type === 'expense_with_transfer';
            })
            ->sum(function($e) {
                if ($e->type?->value === 'expense_with_transfer') {
                    return abs((float) $e->entries[2]->amount);
                }
                return $e->entries->sum(fn($entry) => abs((float) $entry->amount));
            });

        $balance = $totalIncome - $totalExpense;

        $consolidatedIncome = $events->filter(function($e) {
                if (!$e->consolidated) return false;
                $type = $e->type?->value;
                return $type === 'income' || $type === 'income_with_transfer';
            })
            ->sum(function($e) {
                if ($e->type?->value === 'income_with_transfer') {
                    return max(0, (float) $e->entries[0]->amount);
                }
                return $e->entries->sum(fn($entry) => max(0, (float) $entry->amount));
            });

        $consolidatedExpense = $events->filter(function($e) {
                if (!$e->consolidated) return false;
                $type = $e->type?->value;
                return $type === 'expense' || $type === 'expense_with_transfer';
            })
            ->sum(function($e) {
                if ($e->type?->value === 'expense_with_transfer') {
                    return abs((float) $e->entries[2]->amount);
                }
                return $e->entries->sum(fn($entry) => abs((float) $entry->amount));
            });

        $consolidatedBalance = $consolidatedIncome - $consolidatedExpense;

        $isMonthClosed = $this->monthClosureService->isMonthClosed($monthDate->year, $monthDate->month);

        if ($filter !== 'all') {
            $filterTypes = \App\Enums\EventType::filterTypes($filter);
            $events = $events->filter(fn($e) => in_array($e->type?->value, $filterTypes))->values();
        }

        $headerOptions = $this->buildHeaderOptions($monthDate);

        array_unshift($headerOptions, [
            'type' => 'link',
            'url' => url('/entries/create?month=' . $currentMonth),
            'label' => __('entries.create'),
            'icon' => 'bi bi-plus-circle',
        ]);

        return view('entries.index', [
            'events' => $events,
            'currentMonth' => $currentMonth,
            'pageTitle' => __('entries.title'),
            'headerOptions' => $headerOptions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'consolidatedIncome' => $consolidatedIncome,
            'consolidatedExpense' => $consolidatedExpense,
            'consolidatedBalance' => $consolidatedBalance,
            'currentFilter' => $filter,
            'isMonthClosed' => $isMonthClosed,
        ]);
    }

    public function consolidate(int $id): JsonResponse
    {
        try {
            $event = $this->consolidationService->consolidateEvent($id);

            return response()->json($event);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function unconsolidate(int $id): JsonResponse
    {
        try {
            $event = $this->consolidationService->unconsolidateEvent($id);

            return response()->json($event);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $year, int $month)
    {
        $events = $this->eventService->listByMonth($year, $month);

        return response()->json($events);
    }

    private function buildHeaderOptions(\Carbon\Carbon $monthDate): array
    {
        $options = [];

        $isMonthClosed = $this->monthClosureService->isMonthClosed($monthDate->year, $monthDate->month);
        $canClose = $this->monthClosureService->canCloseMonth($monthDate->year, $monthDate->month);
        $lastClosedMonth = $this->monthClosureService->getLastClosedMonth();

        if ($canClose && !$isMonthClosed) {
            $options[] = [
                'type' => 'form',
                'action' => url('/months/' . $monthDate->year . '/' . $monthDate->month . '/close'),
                'method' => 'POST',
                'label' => __('entries.actions.close_month'),
                'icon' => 'bi bi-lock',
                'confirm' => __('messages.confirm.close_month'),
            ];
        }

        if ($lastClosedMonth && $monthDate->equalTo($lastClosedMonth)) {
            $options[] = [
                'type' => 'form',
                'action' => url('/months/reopen'),
                'method' => 'POST',
                'label' => __('entries.actions.reopen_month'),
                'icon' => 'bi bi-unlock',
                'confirm' => __('messages.confirm.reopen_month'),
            ];
        }

        if ($isMonthClosed) {
            $options[] = [
                'type' => 'status',
                'label' => __('entries.status.month_closed'),
                'icon' => 'bi bi-lock-fill',
            ];
        }

        return $options;
    }
}
