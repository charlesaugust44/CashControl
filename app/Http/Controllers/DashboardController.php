<?php

namespace App\Http\Controllers;

use App\Helpers\Formatter;
use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private BalanceService $balanceService;
    private Formatter $fmt;

    public function __construct()
    {
        $this->balanceService = new BalanceService();
        $this->fmt = new Formatter();
    }

    public function index(Request $request): View
    {
        $now = now();
        $totalBalance = $this->balanceService->getTotalBalance();
        $monthlyTotals = $this->balanceService->getMonthlyTotals($now->year, $now->month);
        $nextMonth = $now->copy()->addMonth();
        $forecastBalance = 0;
        foreach (\App\Models\Asset::all() as $asset) {
            $forecastBalance += $this->balanceService->getForecastBalance($asset, $nextMonth->year, $nextMonth->month);
        }

        $balanceHistory = $this->balanceService->getBalanceHistoryAggregated(6);
        $monthlyBreakdown = $this->balanceService->getMonthlyBreakdown(6);
        $pendingConsolidations = $this->balanceService->getPendingConsolidations();

        $prevMonthTotals = $this->balanceService->getMonthlyTotals(
            $now->copy()->subMonth()->year,
            $now->copy()->subMonth()->month
        );

        $incomeTrend = null;
        $incomeTrendDir = null;
        if ($prevMonthTotals['income'] > 0) {
            $diff = $monthlyTotals['income'] - $prevMonthTotals['income'];
            $incomeTrend = number_format(abs(round(($diff / $prevMonthTotals['income']) * 100, 1))) . '%';
            $incomeTrendDir = $diff >= 0 ? 'up' : 'down';
        }

        $expenseTrend = null;
        $expenseTrendDir = null;
        if ($prevMonthTotals['expense'] > 0) {
            $diff = $monthlyTotals['expense'] - $prevMonthTotals['expense'];
            $expenseTrend = number_format(abs(round(($diff / $prevMonthTotals['expense']) * 100, 1))) . '%';
            $expenseTrendDir = $diff >= 0 ? 'up' : 'down';
        }

        return view('dashboard', [
            'pageTitle' => __('ui.dashboard'),
            'totalBalance' => $totalBalance,
            'monthlyIncome' => $monthlyTotals['income'],
            'monthlyExpense' => $monthlyTotals['expense'],
            'forecastBalance' => $forecastBalance,
            'balanceHistory' => $balanceHistory,
            'monthlyBreakdown' => $monthlyBreakdown,
            'pendingConsolidations' => $pendingConsolidations,
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
}
