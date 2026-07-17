<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class HealthController extends Controller
{
    public function __invoke(
        HealthCheckService $service,
    ): JsonResponse {
        $result = $service->check();

        return response()->json(
            $result->toArray(),
            $result->isHealthy()
                ? Response::HTTP_OK
                : Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
