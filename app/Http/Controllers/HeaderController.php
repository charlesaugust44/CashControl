<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Event;
use App\Services\HeaderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeaderController extends Controller
{
    private HeaderService $headerService;

    public function __construct()
    {
        $this->headerService = new HeaderService;
    }

    public function index(): View
    {
        $headers = $this->headerService->list();

        return view('templates.index', [
            'headers' => $headers,
            'pageTitle' => __('templates.title'),
        ]);
    }

    public function create(): View
    {
        $assets = Asset::orderBy('name')->get();

        return view('templates.form', [
            'assets' => $assets,
            'pageTitle' => __('templates.new'),
            'breadcrumbs' => [
                ['label' => __('templates.title'), 'url' => '/templates'],
                ['label' => __('templates.new'), 'url' => null],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'type' => 'required|string|in:income,expense,transfer',
            'rule' => 'required|string|in:fixed,max_last_five_months,mean_last_five_months',
            'default_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'asset_id' => 'required|exists:assets,id',
            'destination_asset_id' => 'nullable|exists:assets,id|different:asset_id',
        ]);

        $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date'])->firstOfMonth();
        if (!empty($validated['end_date'])) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date'])->firstOfMonth();
        }

        $this->headerService->create($validated);

        return redirect('/templates')->with('success', __('messages.success.created', ['item' => __('templates.singular')]));
    }

    public function show(int $id): View
    {
        $header = $this->headerService->get($id);

        return view('templates.show', [
            'header' => $header,
            'pageTitle' => $header->name,
            'breadcrumbs' => [
                ['label' => __('templates.title'), 'url' => '/templates'],
                ['label' => $header->name, 'url' => null],
            ],
        ]);
    }

    public function edit(int $id): View
    {
        $header = $this->headerService->get($id);
        $assets = Asset::orderBy('name')->get();
        $futureEvents = $this->headerService->futurePersistedEvents($id);

        return view('templates.form', [
            'header' => $header,
            'assets' => $assets,
            'futureEvents' => $futureEvents,
            'pageTitle' => __('templates.edit') . " {$header->name}",
            'breadcrumbs' => [
                ['label' => __('templates.title'), 'url' => '/templates'],
                ['label' => $header->name, 'url' => "/templates/{$id}"],
                ['label' => __('ui.edit'), 'url' => null],
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'type' => 'required|string|in:income,expense,transfer',
            'rule' => 'required|string|in:fixed,max_last_five_months,mean_last_five_months',
            'default_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'asset_id' => 'required|exists:assets,id',
            'destination_asset_id' => 'nullable|exists:assets,id|different:asset_id',
            'delete_events' => 'nullable|array',
            'delete_events.*' => 'exists:events,id',
        ]);

        $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date'])->firstOfMonth();
        if (!empty($validated['end_date'])) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date'])->firstOfMonth();
        }

        $deleteEvents = $validated['delete_events'] ?? [];
        unset($validated['delete_events']);

        $this->headerService->update($id, $validated);

        if (!empty($deleteEvents)) {
            Event::whereIn('id', $deleteEvents)->delete();
        }

        return redirect("/templates/{$id}")->with('success', __('messages.success.updated', ['item' => __('templates.singular')]));
    }

    public function delete(int $id): View
    {
        $header = $this->headerService->get($id);
        $futureEvents = $this->headerService->futurePersistedEvents($id);

        return view('templates.delete', [
            'header' => $header,
            'futureEvents' => $futureEvents,
            'pageTitle' => __('templates.delete') . " {$header->name}",
            'breadcrumbs' => [
                ['label' => __('templates.title'), 'url' => '/templates'],
                ['label' => $header->name, 'url' => "/templates/{$id}"],
                ['label' => __('ui.delete'), 'url' => null],
            ],
        ]);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $deleteEvents = $request->input('delete_events', []);

        if (!empty($deleteEvents)) {
            Event::whereIn('id', $deleteEvents)->delete();
        }

        $this->headerService->delete($id);

        return redirect('/templates')->with('success', __('messages.success.deleted', ['item' => __('templates.singular')]));
    }
}
