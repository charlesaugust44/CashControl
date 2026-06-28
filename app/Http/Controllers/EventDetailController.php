<?php

namespace App\Http\Controllers;

use App\Services\ConsolidationService;
use App\Services\EventDetailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventDetailController extends Controller
{
    protected EventDetailService $eventDetailService;
    protected ConsolidationService $consolidationService;

    public function __construct()
    {
        $this->eventDetailService = new EventDetailService();
        $this->consolidationService = new ConsolidationService();
    }

    public function show(int $id): View
    {
        $event = $this->eventDetailService->getEvent($id);
        $assets = $this->eventDetailService->getAssets();
        $eventDate = \Carbon\Carbon::parse($event->date);

        return view('entries.show', [
            'event' => $event,
            'assets' => $assets,
            'isVirtual' => false,
            'pageTitle' => $event->header->name ?? 'Event Details',
            'breadcrumbs' => [
                ['label' => 'Entries', 'url' => '/entries?month=' . $eventDate->format('Y-m')],
                ['label' => $event->header->name ?? 'Event Details', 'url' => null],
            ],
        ]);
    }

    public function showVirtual(int $headerId, int $year, int $month): View
    {
        $event = $this->eventDetailService->getVirtualEvent($headerId, $year, $month);

        if (!$event) {
            abort(404, 'Event not found');
        }

        $assets = $this->eventDetailService->getAssets();

        return view('entries.show', [
            'event' => $event,
            'assets' => $assets,
            'isVirtual' => true,
            'headerId' => $headerId,
            'year' => $year,
            'month' => $month,
            'pageTitle' => $event->header->name ?? 'Event Details',
            'breadcrumbs' => [
                ['label' => 'Entries', 'url' => '/entries?month=' . \Carbon\Carbon::create($year, $month, 1)->format('Y-m')],
                ['label' => $event->header->name ?? 'Event Details', 'url' => null],
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $action = $request->input('action', 'submit');

            if ($action === 'unconsolidate') {
                $event = $this->consolidationService->unconsolidateEvent($id);
                return redirect('/entries/' . $event->id)
                    ->with('success', 'Event unconsolidated successfully');
            }

            $validated = $this->validateEventData($request);
            $event = $this->eventDetailService->updateEvent($id, $validated);

            if ($action === 'consolidate') {
                $event = $this->consolidationService->consolidateEvent($event->id);
                return redirect('/entries/' . $event->id)
                    ->with('success', 'Event consolidated successfully');
            }

            if ($action === 'save') {
                return redirect('/entries/' . $event->id)
                    ->with('success', 'Event saved successfully');
            }

            $eventDate = \Carbon\Carbon::parse($event->date);

            return redirect('/entries?month=' . $eventDate->format('Y-m'))
                ->with('success', 'Event updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request, int $headerId, int $year, int $month): RedirectResponse
    {
        try {
            $validated = $this->validateEventData($request);
            $event = $this->eventDetailService->persistVirtualEvent($headerId, $year, $month, $validated);

            $action = $request->input('action', 'submit');

            if ($action === 'save') {
                return redirect('/entries/' . $event->id)
                    ->with('success', 'Event saved successfully');
            }

            return redirect('/entries?month=' . \Carbon\Carbon::create($year, $month, 1)->format('Y-m'))
                ->with('success', 'Event saved successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $event = $this->eventDetailService->getEvent($id);
            $eventDate = \Carbon\Carbon::parse($event->date);

            $this->eventDetailService->deleteEvent($id);

            return redirect('/entries?month=' . $eventDate->format('Y-m'))
                ->with('success', 'Event deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    private function validateEventData(Request $request): array
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:300',
            'entries' => 'required|array|min:1',
            'entries.*.asset_id' => 'required|exists:assets,id',
            'entries.*.amount' => 'required|numeric',
        ]);

        return $validated;
    }
}
