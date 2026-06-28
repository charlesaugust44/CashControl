<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    private AssetService $assetService;

    public function __construct()
    {
        $this->assetService = new AssetService();
    }

    public function index(): View
    {
        $assets = $this->assetService->list();
        $total = $assets->sum('balance');

        return view('assets.index', [
            'assets' => $assets,
            'total' => $total,
            'pageTitle' => 'Assets',
        ]);
    }

    public function create(): View
    {
        return view('assets.form', [
            'pageTitle' => 'New Asset',
        ]);
    }

    public function show(string $id, Request $request): View
    {
        $asset = $this->assetService->get($id);
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $events = $this->assetService->entries($id);

        return view('assets.show', [
            'asset' => $asset,
            'events' => $events,
            'currentMonth' => $currentMonth,
            'pageTitle' => $asset->name,
        ]);
    }

    public function edit(string $id): View
    {
        $asset = $this->assetService->get($id);

        return view('assets.form', [
            'asset' => $asset,
            'pageTitle' => "Edit {$asset->name}",
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
                ->with('success', 'Asset saved successfully');
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
                ->with('success', 'Asset saved successfully');
        }

        return redirect('/assets');
    }
}
