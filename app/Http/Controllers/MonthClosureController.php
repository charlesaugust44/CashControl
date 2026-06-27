<?php

namespace App\Http\Controllers;

use App\Services\MonthClosureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class MonthClosureController extends Controller
{
    protected MonthClosureService $monthClosureService;

    public function __construct()
    {
        $this->monthClosureService = new MonthClosureService();
    }

    public function close(int $year, int $month): RedirectResponse
    {
        try {
            $this->monthClosureService->closeMonth($year, $month);

            return redirect()->back()->with('success', "Month {$year}-{$month} closed successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reopen(): RedirectResponse
    {
        try {
            $this->monthClosureService->reopenLastMonth();

            return redirect()->back()->with('success', 'Last month reopened successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function status(): JsonResponse
    {
        $lastClosedMonth = $this->monthClosureService->getLastClosedMonth();

        return response()->json([
            'last_closed_month' => $lastClosedMonth?->format('Y-m'),
            'is_closed' => $lastClosedMonth !== null,
        ]);
    }
}
