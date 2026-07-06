<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Models\Event;
use App\Services\ConsolidationService;
use App\Services\EventDetailService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventDetailController extends Controller
{
    protected EventDetailService $eventDetailService;

    protected ConsolidationService $consolidationService;

    public function __construct()
    {
        $this->eventDetailService = new EventDetailService;
        $this->consolidationService = new ConsolidationService;
    }

    public function create(Request $request): View
    {
        $assets = $this->eventDetailService->getAssets();
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $defaultDate = Carbon::parse($currentMonth)->startOfMonth();

        return view('entries.create', [
            'assets' => $assets,
            'defaultDate' => $defaultDate,
            'currentMonth' => $currentMonth,
            'pageTitle' => __('entries.create'),
            'entryStructures' => $this->buildEntryStructures(),
            'breadcrumbs' => [
                ['label' => __('entries.title'), 'url' => '/entries?month='.$currentMonth],
                ['label' => __('entries.create'), 'url' => null],
            ],
        ]);
    }

    public function storeStandalone(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateStandaloneEventData($request);
            $event = $this->eventDetailService->createStandaloneEvent($validated);

            $action = $request->input('action', 'submit');

            if ($action === 'save') {
                return redirect('/entries/'.$event->id)
                    ->with('success', __('messages.success.saved', ['item' => __('entries.singular')]));
            }

            $eventDate = Carbon::parse($event->date);

            return redirect('/entries?month='.$eventDate->format('Y-m'))
                ->with('success', __('messages.success.saved', ['item' => __('entries.singular')]));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $event = $this->eventDetailService->getEvent($id);
        $assets = $this->eventDetailService->getAssets();
        $eventDate = Carbon::parse($event->date);

        return view('entries.show', [
            'event' => $event,
            'assets' => $assets,
            'isVirtual' => false,
            'pageTitle' => $event->name ?? __('entries.title'),
            'entryStructures' => $this->buildEntryStructures(),
            'breadcrumbs' => [
                ['label' => __('entries.title'), 'url' => '/entries?month='.$eventDate->format('Y-m')],
                ['label' => $event->name ?? __('entries.title'), 'url' => null],
            ],
        ]);
    }

    public function delete(int $id): View
    {
        $event = $this->eventDetailService->getEvent($id);
        $eventDate = Carbon::parse($event->date);

        return view('entries.delete', [
            'event' => $event,
            'pageTitle' => __('entries.delete_confirmation.title'),
            'breadcrumbs' => [
                ['label' => __('entries.title'), 'url' => '/entries?month='.$eventDate->format('Y-m')],
                ['label' => $event->name ?? __('entries.title'), 'url' => '/entries/'.$id],
                ['label' => __('ui.delete'), 'url' => null],
            ],
        ]);
    }

    public function showVirtual(int $headerId, int $year, int $month): View
    {
        $event = $this->eventDetailService->getVirtualEvent($headerId, $year, $month);

        if (! $event) {
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
            'pageTitle' => $event->name ?? __('entries.title'),
            'entryStructures' => $this->buildEntryStructures(),
            'breadcrumbs' => [
                ['label' => __('entries.title'), 'url' => '/entries?month='.Carbon::create($year, $month, 1)->format('Y-m')],
                ['label' => $event->name ?? __('entries.title'), 'url' => null],
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $action = $request->input('action', 'submit');

            if ($action === 'unconsolidate') {
                $this->consolidationService->unconsolidateEvent($id);

                return redirect('/entries/'.$id)
                    ->with('success', __('messages.success.reverted'));
            }

            $event = $this->eventDetailService->getEvent($id);
            $validated = $this->validateEventData($request, $action, $event);
            $event = $this->eventDetailService->updateEvent($id, $validated);

            if (in_array($action, ['consolidate', 'consolidate_expense_income', 'consolidate_transfer'])) {
                match ($action) {
                    'consolidate' => $this->consolidationService->consolidateEvent($id),
                    'consolidate_expense_income' => $this->consolidationService->consolidateExpenseIncome($id),
                    'consolidate_transfer' => $this->consolidationService->consolidateTransfer($id),
                };

                return redirect('/entries/'.$id)
                    ->with('success', __('messages.success.consolidated'));
            }

            if ($action === 'save') {
                return redirect('/entries/'.$id)
                    ->with('success', __('messages.success.saved', ['item' => __('entries.singular')]));
            }

            $eventDate = Carbon::parse($event->date);

            return redirect('/entries?month='.$eventDate->format('Y-m'))
                ->with('success', __('messages.success.updated', ['item' => __('entries.singular')]));
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

            if ($action === 'consolidate') {
                $event = $this->consolidationService->consolidateEvent($event->id);

                return redirect('/entries/'.$event->id)
                    ->with('success', __('messages.success.consolidated'));
            }

            if ($action === 'consolidate_expense_income') {
                $event = $this->consolidationService->consolidateExpenseIncome($event->id);

                return redirect('/entries/'.$event->id)
                    ->with('success', __('messages.success.consolidated'));
            }

            if ($action === 'consolidate_transfer') {
                $event = $this->consolidationService->consolidateTransfer($event->id);

                return redirect('/entries/'.$event->id)
                    ->with('success', __('messages.success.consolidated'));
            }

            if ($action === 'save') {
                return redirect('/entries/'.$event->id)
                    ->with('success', __('messages.success.saved', ['item' => __('entries.singular')]));
            }

            return redirect('/entries?month='.Carbon::create($year, $month, 1)->format('Y-m'))
                ->with('success', __('messages.success.saved', ['item' => __('entries.singular')]));
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
            $eventDate = Carbon::parse($event->date);

            $this->eventDetailService->deleteEvent($id);

            return redirect('/entries?month='.$eventDate->format('Y-m'))
                ->with('success', __('messages.success.deleted', ['item' => __('entries.singular')]));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    private function validateEventData(Request $request, string $action = 'submit', ?Event $event = null): array
    {
        $rules = [
            'note' => 'nullable|string|max:300',
            'due_day' => 'nullable|integer|min:1|max:31',
            'positions' => 'nullable|array',
            'positions.*' => 'integer',
        ];

        if ($event && $event->isComposite() && $event->isPartiallyConsolidated()) {
            if ($action === 'consolidate_transfer') {
                $indices = $event->getTransferEntryIndices();
                foreach ($indices as $index) {
                    $rules["entries.{$index}.asset_id"] = 'required|exists:assets,id';
                    $rules["entries.{$index}.amount"] = 'required|numeric';
                }
            } elseif ($action === 'consolidate_expense_income') {
                $indices = $event->getIncomeExpenseEntryIndices();
                foreach ($indices as $index) {
                    $rules["entries.{$index}.asset_id"] = 'required|exists:assets,id';
                    $rules["entries.{$index}.amount"] = 'required|numeric';
                }
            } else {
                $rules['entries'] = 'required|array|min:1';
                $rules['entries.*.asset_id'] = 'required|exists:assets,id';
                $rules['entries.*.amount'] = 'required|numeric';
            }
        } else {
            $rules['entries'] = 'required|array|min:1';
            $rules['entries.*.asset_id'] = 'required|exists:assets,id';
            $rules['entries.*.amount'] = 'required|numeric';
        }

        return $request->validate($rules);
    }

    private function validateStandaloneEventData(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense,transfer,expense_with_transfer,income_with_transfer',
            'date' => 'required|date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'note' => 'nullable|string|max:300',
            'entries' => 'required|array|min:1',
            'entries.*.asset_id' => 'required|exists:assets,id',
            'entries.*.amount' => 'required|numeric',
        ]);

        return $validated;
    }

    private function buildEntryStructures(): array
    {
        $entryStructures = [];
        foreach (EventType::cases() as $type) {
            $structure = [];
            for ($i = 0; $i < $type->entryCount(); $i++) {
                $structure[] = [
                    'sign' => $type->entrySign($i),
                    'slot' => $type->entryAssetSlot($i),
                ];
            }
            $entryStructures[$type->value] = $structure;
        }

        return $entryStructures;
    }
}
