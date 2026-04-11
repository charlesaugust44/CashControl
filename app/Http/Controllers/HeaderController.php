<?php

namespace App\Http\Controllers;

use App\Services\HeaderService;
use Illuminate\Http\JsonResponse;

class HeaderController extends Controller
{
    private HeaderService $headerService;

    public function __construct()
    {
        $this->headerService = new HeaderService;
    }

    public function index(): JsonResponse
    {
        $headers = $this->headerService->active();

        return response()->json($headers);
    }
}
