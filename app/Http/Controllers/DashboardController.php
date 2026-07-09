<?php

namespace App\Http\Controllers;

use App\Helpers\Formatter;
use App\Models\Asset;
use App\Services\BalanceService;
use App\Services\MonthClosureService;
use App\Support\UnityContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private BalanceService $balanceService;
    private MonthClosureService $monthClosureService;
    private Formatter $fmt;
    private UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->balanceService = new BalanceService($unityContext);
        $this->monthClosureService = new MonthClosureService($unityContext);
        $this->fmt = new Formatter();
    }

    public function index(Request $request): View
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $monthDate = \Carbon\Carbon::parse($currentMonth);
        $pendingTypeFilter = $request->get('pending_type', '');
        $pendingAssetFilter = $request->get('pending_asset', '');

        $isMonthClosed = $this->monthClosureService->isMonthClosed($monthDate->year, $monthDate->month);

        $totalBalance = $this->balanceService->getTotalBalance();
        $monthlyTotalsSplit = $this->balanceService->getMonthlyTotalsSplit($monthDate->year, $monthDate->month);

        $forecastBalance = 0;
        foreach ($this->getScopedAssets() as $asset) {
            $forecastBalance += $this->balanceService->getForecastBalance($asset, $monthDate->year, $monthDate->month);
        }

        $balanceHistory = $this->balanceService->getBalanceHistoryAggregated(6, $monthDate);
        $monthlyBreakdown = $this->balanceService->getMonthlyBreakdown(6, $monthDate);
        $pendingConsolidations = $this->balanceService->getPendingConsolidations($monthDate->year, $monthDate->month);

        if (!empty($pendingTypeFilter)) {
            $filterTypes = \App\Enums\EventType::filterTypes($pendingTypeFilter);
            $pendingConsolidations = $pendingConsolidations->filter(fn($e) => in_array($e->type?->value, $filterTypes));
        }

        if (!empty($pendingAssetFilter)) {
            $pendingConsolidations = $pendingConsolidations->filter(function ($e) use ($pendingAssetFilter) {
                return $e->entries->contains('asset_id', (int) $pendingAssetFilter);
            });
        }

        $pendingConsolidations = $pendingConsolidations->values();

        $prevMonthTotals = $this->balanceService->getMonthlyTotals(
            $monthDate->copy()->subMonth()->year,
            $monthDate->copy()->subMonth()->month
        );

        $forecastedIncome = $monthlyTotalsSplit['consolidated']['income'] + $monthlyTotalsSplit['unconsolidated']['income'];
        $forecastedExpense = $monthlyTotalsSplit['consolidated']['expense'] + $monthlyTotalsSplit['unconsolidated']['expense'];

        $incomeTrend = null;
        $incomeTrendDir = null;
        if ($prevMonthTotals['income'] > 0) {
            $diff = $forecastedIncome - $prevMonthTotals['income'];
            $incomeTrend = number_format(abs(round(($diff / $prevMonthTotals['income']) * 100, 1))) . '%';
            $incomeTrendDir = $diff >= 0 ? 'up' : 'down';
        }

        $expenseTrend = null;
        $expenseTrendDir = null;
        if ($prevMonthTotals['expense'] > 0) {
            $diff = $forecastedExpense - $prevMonthTotals['expense'];
            $expenseTrend = number_format(abs(round(($diff / $prevMonthTotals['expense']) * 100, 1))) . '%';
            $expenseTrendDir = $diff >= 0 ? 'up' : 'down';
        }

        $assetsQuery = Asset::orderBy('name');
        if ($this->unityContext->has()) {
            $assetsQuery->where('unity_id', $this->unityContext->id());
        }
        $assets = $assetsQuery->get();

        return view('dashboard', [
            'pageTitle' => __('ui.dashboard'),
            'currentMonth' => $currentMonth,
            'monthDate' => $monthDate,
            'isMonthClosed' => $isMonthClosed,
            'totalBalance' => $totalBalance,
            'consolidatedIncome' => $monthlyTotalsSplit['consolidated']['income'],
            'consolidatedExpense' => $monthlyTotalsSplit['consolidated']['expense'],
            'forecastedIncome' => $forecastedIncome,
            'forecastedExpense' => $forecastedExpense,
            'forecastBalance' => $forecastBalance,
            'balanceHistory' => $balanceHistory,
            'monthlyBreakdown' => $monthlyBreakdown,
            'pendingConsolidations' => $pendingConsolidations,
            'pendingTypeFilter' => $pendingTypeFilter,
            'pendingAssetFilter' => $pendingAssetFilter,
            'assets' => $assets,
            'incomeTrend' => $incomeTrend,
            'incomeTrendDir' => $incomeTrendDir,
            'expenseTrend' => $expenseTrend,
            'expenseTrendDir' => $expenseTrendDir,
            'fmt' => $this->fmt,
        ]);
    }

    public function dismiss(Request $request): RedirectResponse
    {
        $eventId = $request->input('event_id');
        $headerId = $request->input('header_id');
        $key = $headerId . '-' . $eventId;

        $dismissed = $request->session()->get('dismissed_alerts', []);
        if (!in_array($key, $dismissed)) {
            $dismissed[] = $key;
        }
        $request->session()->put('dismissed_alerts', $dismissed);

        return redirect()->back();
    }

    public function markRead(Request $request): RedirectResponse
    {
        $eventId = $request->input('event_id');
        $headerId = $request->input('header_id');
        $key = $headerId . '-' . $eventId;

        $readAlerts = $request->session()->get('read_alerts', []);
        if (!in_array($key, $readAlerts)) {
            $readAlerts[] = $key;
        }
        $request->session()->put('read_alerts', $readAlerts);

        return redirect()->back();
    }

    private function getScopedAssets(): \Illuminate\Support\Collection
    {
        $query = Asset::query();

        if ($this->unityContext->has()) {
            $query->where('unity_id', $this->unityContext->id());
        }

        return $query->get();
    }
}
