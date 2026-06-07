<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;

class AssetController extends Controller
{
    private AssetService $assetService;

    public function __construct()
    {
        $this->assetService = new AssetService();
    }

    public function index(): JsonResponse
    {
        $assets = $this->assetService->list();

        return response()->json($assets);
    }

    public function show(string $id): JsonResponse
    {
        $asset = $this->assetService->get($id);

        return response()->json($asset);
    }

    public function entries(string $id): JsonResponse
    {
        $assets = $this->assetService->entries($id);

        return response()->json($assets);
    }

    public function store()
    {
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        $asset = $this->assetService->create($validated);

        return response()->json($asset, 201);
    }

    public function update(string $id)
    {
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        $asset = $this->assetService->update($id, $validated);

        return response()->json($asset);
    }
}
