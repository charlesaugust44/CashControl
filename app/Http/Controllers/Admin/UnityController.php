<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UnityService;
use App\Support\UnityContext;
use Illuminate\Http\Request;

class UnityController extends Controller
{
    protected UnityService $service;

    public function __construct(UnityContext $unityContext)
    {
        $this->service = new UnityService($unityContext);
    }

    public function index()
    {
        $unities = $this->service->getAll();

        return view('admin.unities.index', [
            'unities' => $unities,
            'pageTitle' => __('admin.unities.title'),
            'breadcrumbs' => [
                ['label' => __('admin.unities.title'), 'url' => null],
            ],
        ]);
    }

    public function create()
    {
        return view('admin.unities.form', [
            'pageTitle' => __('admin.unities.create_unity'),
            'breadcrumbs' => [
                ['label' => __('admin.unities.title'), 'url' => route('admin.unities.index')],
                ['label' => __('admin.unities.create'), 'url' => null],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:unities,name',
            'description' => 'nullable|string|max:500',
        ]);

        $this->service->create($validated);

        return redirect()->route('admin.unities.index')
            ->with('success', __('admin.unities.create').' - OK');
    }

    public function show(int $id)
    {
        $unity = $this->service->getById($id);
        $unity->load('users');
        $availableUsers = $this->service->getAvailableUsers($id);

        return view('admin.unities.show', [
            'unity' => $unity,
            'availableUsers' => $availableUsers,
            'pageTitle' => $unity->name,
            'breadcrumbs' => [
                ['label' => __('admin.unities.title'), 'url' => route('admin.unities.index')],
                ['label' => $unity->name, 'url' => null],
            ],
        ]);
    }

    public function edit(int $id)
    {
        $unity = $this->service->getById($id);

        return view('admin.unities.form', [
            'unity' => $unity,
            'pageTitle' => __('admin.unities.edit_unity').': '.$unity->name,
            'breadcrumbs' => [
                ['label' => __('admin.unities.title'), 'url' => route('admin.unities.index')],
                ['label' => $unity->name, 'url' => route('admin.unities.show', $unity->id)],
                ['label' => __('admin.unities.edit'), 'url' => null],
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:unities,name,'.$id,
            'description' => 'nullable|string|max:500',
        ]);

        $this->service->update($id, $validated);

        return redirect()->route('admin.unities.index')
            ->with('success', 'Unity updated successfully');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);

            return redirect()->route('admin.unities.index')
                ->with('success', 'Unity deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.unities.index')
                ->with('error', $e->getMessage());
        }
    }

    public function assign(Request $request, int $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->service->assignUser($id, $request->user_id);

        return redirect()->route('admin.unities.show', $id)
            ->with('success', 'User assigned successfully');
    }

    public function unassign(int $id, int $userId)
    {
        $this->service->unassignUser($id, $userId);

        return redirect()->route('admin.unities.show', $id)
            ->with('success', 'User unassigned successfully');
    }
}
