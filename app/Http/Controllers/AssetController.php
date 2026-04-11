<?php

namespace App\Http\Controllers;

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

    public function entries(int $id): JsonResponse
    {
        $assets = $this->assetService->entries($id);

        return response()->json($assets);
    }
}
