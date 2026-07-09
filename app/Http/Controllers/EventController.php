<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Models\Asset;
use App\Services\ConsolidationService;
use App\Services\EventService;
use App\Services\MonthClosureService;
use App\Support\UnityContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    protected EventService $eventService;

    protected ConsolidationService $consolidationService;

    protected MonthClosureService $monthClosureService;

    protected UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->eventService = new EventService($unityContext);
        $this->consolidationService = new ConsolidationService($unityContext);
        $this->monthClosureService = new MonthClosureService($unityContext);
    }

    public function index(Request $request): View
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $monthDate = Carbon::parse($currentMonth);
        $typeFilter = $request->get('type', '');
        $consolidatedFilter = $request->get('consolidated', '');
        $assetFilter = $request->get('asset', '');

        $events = $this->eventService->listByMonth(
            $monthDate->year,
            $monthDate->month
        );

        $totalIncome = $events->filter(function ($e) {
            $type = $e->type?->value;

            return $type === 'income' || $type === 'income_with_transfer';
        })
            ->sum(fn ($e) => $e->getIncomeAmount());

        $totalExpense = $events->filter(function ($e) {
            $type = $e->type?->value;

            return $type === 'expense' || $type === 'expense_with_transfer';
        })
            ->sum(fn ($e) => $e->getExpenseAmount());

        $balance = $totalIncome - $totalExpense;

        $consolidatedIncome = $events->filter(function ($e) {
            if (! $e->consolidated) {
                return false;
            }
            $type = $e->type?->value;

            return $type === 'income' || $type === 'income_with_transfer';
        })
            ->sum(fn ($e) => $e->getIncomeAmount());

        $consolidatedExpense = $events->filter(function ($e) {
            if (! $e->consolidated) {
                return false;
            }
            $type = $e->type?->value;

            return $type === 'expense' || $type === 'expense_with_transfer';
        })
            ->sum(fn ($e) => $e->getExpenseAmount());

        $consolidatedBalance = $consolidatedIncome - $consolidatedExpense;

        $isMonthClosed = $this->monthClosureService->isMonthClosed($monthDate->year, $monthDate->month);

        if (!empty($typeFilter)) {
            $filterTypes = EventType::filterTypes($typeFilter);
            $events = $events->filter(fn ($e) => in_array($e->type?->value, $filterTypes));
        }

        if (!empty($consolidatedFilter)) {
            $filterConfig = EventType::consolidatedFilter($consolidatedFilter);
            if (!empty($filterConfig)) {
                $events = $events->filter(function ($e) use ($filterConfig) {
                    $type = $e->type?->value;
                    if (!in_array($type, $filterConfig['types'])) {
                        return false;
                    }
                    if ($filterConfig['consolidated'] !== null && $e->consolidated !== $filterConfig['consolidated']) {
                        return false;
                    }
                    if ($filterConfig['transfer_consolidated'] !== null && $e->transfer_consolidated !== $filterConfig['transfer_consolidated']) {
                        return false;
                    }
                    return true;
                });
            }
        }

        if (!empty($assetFilter)) {
            $events = $events->filter(function ($e) use ($assetFilter) {
                return $e->entries->contains('asset_id', (int) $assetFilter);
            });
        }

        $events = $events->values();

        $headerOptions = $this->buildHeaderOptions($monthDate);

        array_unshift($headerOptions, [
            'type' => 'link',
            'url' => url('/entries/create?month='.$currentMonth),
            'label' => __('entries.create'),
            'icon' => 'bi bi-plus-circle',
        ]);

        $assetsQuery = Asset::orderBy('name');
        if ($this->unityContext->has()) {
            $assetsQuery->where('unity_id', $this->unityContext->id());
        }
        $assets = $assetsQuery->get();

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
            'typeFilter' => $typeFilter,
            'consolidatedFilter' => $consolidatedFilter,
            'assetFilter' => $assetFilter,
            'assets' => $assets,
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

    private function buildHeaderOptions(Carbon $monthDate): array
    {
        $options = [];

        $isMonthClosed = $this->monthClosureService->isMonthClosed($monthDate->year, $monthDate->month);
        $canClose = $this->monthClosureService->canCloseMonth($monthDate->year, $monthDate->month);
        $lastClosedMonth = $this->monthClosureService->getLastClosedMonth();

        if ($canClose && ! $isMonthClosed) {
            $options[] = [
                'type' => 'form',
                'action' => url('/months/'.$monthDate->year.'/'.$monthDate->month.'/close'),
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
