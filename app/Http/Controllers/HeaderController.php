<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Event;
use App\Services\HeaderService;
use Carbon\Carbon;
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

    public function index(Request $request): View
    {
        $typeFilter = $request->get('type', '');
        $assetFilter = $request->get('asset', '');
        $headers = $this->headerService->list();

        if (!empty($typeFilter)) {
            $filterTypes = \App\Enums\EventType::filterTypes($typeFilter);
            $headers = $headers->filter(fn ($h) => in_array($h->type?->value, $filterTypes));
        }

        if (!empty($assetFilter)) {
            $headers = $headers->filter(function ($h) use ($assetFilter) {
                return $h->asset_id === (int) $assetFilter || $h->destination_asset_id === (int) $assetFilter;
            });
        }

        $headers = $headers->values();

        $assets = Asset::orderBy('name')->get();

        return view('templates.index', [
            'headers' => $headers,
            'typeFilter' => $typeFilter,
            'assetFilter' => $assetFilter,
            'assets' => $assets,
            'pageTitle' => __('templates.title'),
            'headerOptions' => [
                [
                    'type' => 'link',
                    'url' => url('/templates/create'),
                    'label' => __('ui.new', ['item' => __('templates.singular')]),
                    'icon' => 'bi bi-plus-circle',
                ],
            ],
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
            'type' => 'required|string|in:income,expense,transfer,expense_with_transfer,income_with_transfer',
            'rule' => 'required|string|in:fixed,max_last_five_months,mean_last_five_months',
            'default_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'asset_id' => 'required|exists:assets,id',
            'destination_asset_id' => 'nullable|exists:assets,id|different:asset_id',
        ]);

        if (in_array($validated['type'], ['transfer', 'expense_with_transfer', 'income_with_transfer'])) {
            $request->validate([
                'destination_asset_id' => 'required|exists:assets,id|different:asset_id',
            ]);
        }

        $validated['start_date'] = Carbon::parse($validated['start_date'])->firstOfMonth();
        if (! empty($validated['end_date'])) {
            $validated['end_date'] = Carbon::parse($validated['end_date'])->firstOfMonth();
        }
        if (empty($validated['due_day'])) {
            $validated['due_day'] = null;
        }

        $this->headerService->create($validated);

        return redirect('/templates')->with('success', __('messages.success.created', ['item' => __('templates.singular')]));
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
            'pageTitle' => __('templates.edit')." {$header->name}",
            'breadcrumbs' => [
                ['label' => __('templates.title'), 'url' => '/templates'],
                ['label' => $header->name, 'url' => null],
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'type' => 'required|string|in:income,expense,transfer,expense_with_transfer,income_with_transfer',
            'rule' => 'required|string|in:fixed,max_last_five_months,mean_last_five_months',
            'default_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'asset_id' => 'required|exists:assets,id',
            'destination_asset_id' => 'nullable|exists:assets,id|different:asset_id',
            'delete_events' => 'nullable|array',
            'delete_events.*' => 'exists:events,id',
        ]);

        if (in_array($validated['type'], ['transfer', 'expense_with_transfer', 'income_with_transfer'])) {
            $request->validate([
                'destination_asset_id' => 'required|exists:assets,id|different:asset_id',
            ]);
        }

        $validated['start_date'] = Carbon::parse($validated['start_date'])->firstOfMonth();
        if (! empty($validated['end_date'])) {
            $validated['end_date'] = Carbon::parse($validated['end_date'])->firstOfMonth();
        }
        if (empty($validated['due_day'])) {
            $validated['due_day'] = null;
        }

        $deleteEvents = $validated['delete_events'] ?? [];
        unset($validated['delete_events']);

        $this->headerService->update($id, $validated);

        if (! empty($deleteEvents)) {
            $eventsToDelete = Event::whereIn('id', $deleteEvents)->get();
            foreach ($eventsToDelete as $event) {
                $event->entries()->delete();
                $event->delete();
            }
        }

        return redirect('/templates')->with('success', __('messages.success.updated', ['item' => __('templates.singular')]));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $deleteEvents = $request->input('delete_events', []);

        $futureEvents = $this->headerService->futurePersistedEvents($id);

        foreach ($futureEvents as $event) {
            if (in_array($event->id, $deleteEvents)) {
                $event->entries()->delete();
                $event->delete();
            } else {
                $event->header_id = null;
                $event->save();
            }
        }

        $this->headerService->delete($id);

        return redirect('/templates')->with('success', __('messages.success.deleted', ['item' => __('templates.singular')]));
    }
}
