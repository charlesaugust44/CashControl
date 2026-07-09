<?php

namespace App\Http\Controllers;

use App\Helpers\Formatter;
use App\Models\Asset;
use App\Services\AssetService;
use App\Services\BalanceService;
use App\Services\EventService;
use App\Support\UnityContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    private AssetService $assetService;
    private BalanceService $balanceService;
    private EventService $eventService;
    private Formatter $fmt;

    public function __construct(UnityContext $unityContext)
    {
        $this->assetService = new AssetService($unityContext);
        $this->balanceService = new BalanceService($unityContext);
        $this->eventService = new EventService($unityContext);
        $this->fmt = new Formatter();
    }

    public function index(): View
    {
        $assets = $this->assetService->list();
        $total = $assets->sum('balance');

        $currentMonth = now()->format('Y-m');
        $monthDate = now();
        $currentMonthLabel = $monthDate->translatedFormat('M Y');

        $forecastedTotal = 0;
        $assetsWithForecast = $assets->map(function ($asset) use ($monthDate, &$forecastedTotal) {
            $forecasted = $this->balanceService->getForecastBalance($asset, $monthDate->year, $monthDate->month);
            $forecastedTotal += $forecasted;
            $asset->forecasted_balance = $forecasted;
            return $asset;
        });

        return view('assets.index', [
            'assets' => $assetsWithForecast,
            'total' => $total,
            'forecastedTotal' => $forecastedTotal,
            'currentMonthLabel' => $currentMonthLabel,
            'pageTitle' => __('assets.title'),
            'fmt' => $this->fmt,
            'headerOptions' => [
                [
                    'type' => 'link',
                    'url' => url('/assets/create'),
                    'label' => __('ui.new', ['item' => __('assets.singular')]),
                    'icon' => 'bi bi-plus-circle',
                ],
            ],
        ]);
    }

    public function create(): View
    {
        return view('assets.form', [
            'pageTitle' => __('assets.new'),
            'breadcrumbs' => [
                ['label' => __('assets.title'), 'url' => '/assets'],
                ['label' => __('assets.new'), 'url' => null],
            ],
        ]);
    }

    public function show(string $id, Request $request): View
    {
        $asset = $this->assetService->get($id);
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $monthDate = \Carbon\Carbon::parse($currentMonth . '-01');
        
        $allEvents = $this->eventService->listByMonth(
            $monthDate->year,
            $monthDate->month
        );

        $events = $allEvents->filter(function ($event) use ($asset) {
            return $event->entries->contains('asset_id', $asset->id);
        })->values();

        $forecastedBalance = $this->balanceService->getForecastBalance(
            $asset,
            $monthDate->year,
            $monthDate->month
        );

        return view('assets.show', [
            'asset' => $asset,
            'events' => $events,
            'currentMonth' => $currentMonth,
            'monthDate' => $monthDate,
            'forecastedBalance' => $forecastedBalance,
            'pageTitle' => $asset->name,
            'fmt' => $this->fmt,
            'headerOptions' => [
                [
                    'type' => 'link',
                    'url' => url("/assets/{$asset->id}/edit"),
                    'label' => __('ui.edit'),
                    'icon' => 'bi bi-pencil',
                ],
            ],
            'breadcrumbs' => [
                ['label' => __('assets.title'), 'url' => '/assets'],
                ['label' => $asset->name, 'url' => null],
            ],
        ]);
    }

    public function edit(string $id): View
    {
        $asset = $this->assetService->get($id);

        return view('assets.form', [
            'asset' => $asset,
            'pageTitle' => __('assets.edit') . " {$asset->name}",
            'breadcrumbs' => [
                ['label' => __('assets.title'), 'url' => '/assets'],
                ['label' => $asset->name, 'url' => '/assets/' . $asset->id],
                ['label' => __('ui.edit'), 'url' => null],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        $asset = $this->assetService->create($validated);

        $action = $request->input('action', 'submit');

        if ($action === 'save') {
            return redirect('/assets/' . $asset->id . '/edit')
                ->with('success', __('messages.success.saved', ['item' => __('assets.singular')]));
        }

        return redirect('/assets');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        $this->assetService->update($id, $validated);

        $action = $request->input('action', 'submit');

        if ($action === 'save') {
            return redirect('/assets/' . $id . '/edit')
                ->with('success', __('messages.success.saved', ['item' => __('assets.singular')]));
        }

        return redirect('/assets');
    }
}
