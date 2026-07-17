<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContactMetricsService;
use Illuminate\Http\JsonResponse;

final class MetricsController extends Controller
{
    public function __invoke(
        ContactMetricsService $service,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $service->snapshot()->toArray(),
        ]);
    }
}
