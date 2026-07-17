<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class LogApiRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        /** @var Response|null $response */
        $response = null;

        try {
            $response = $next($request);

            return $response;
        } finally {
            if ($request->is('api') || $request->is('api/*')) {
                $this->writeLog(
                    request: $request,
                    response: $response,
                    startedAt: $startedAt,
                );
            }
        }
    }

    private function writeLog(
        Request $request,
        ?Response $response,
        float $startedAt,
    ): void {
        $route = $request->route();
        $requestId = $request->attributes->get(
            AssignRequestId::ATTRIBUTE,
        );

        Log::channel('api_requests')->info('api_request', [
            'request_id' => is_string($requestId)
                ? $requestId
                : null,
            'method' => $request->method(),
            'route' => $route instanceof Route
                ? $route->getName()
                : null,
            'status' => $response?->getStatusCode()
                ?? Response::HTTP_INTERNAL_SERVER_ERROR,
            'duration_ms' => (int) round(
                (microtime(true) - $startedAt) * 1000,
            ),
        ]);
    }
}
