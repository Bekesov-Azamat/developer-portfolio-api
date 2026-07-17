<?php

namespace App\Providers;

use App\Contracts\Repositories\ContactSubmissionRepository;
use App\Http\Middleware\AssignRequestId;
use App\Repositories\EloquentContactSubmissionRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ContactSubmissionRepository::class,
            EloquentContactSubmissionRepository::class,
        );
    }

    public function boot(): void
    {
        RateLimiter::for(
            'contact-submissions',
            function (Request $request): Limit {
                $requestId = $request->attributes->get(
                    AssignRequestId::ATTRIBUTE,
                );

                return Limit::perMinute(
                    max(
                        1,
                        (int) config('contact.rate_limit.per_minute', 5),
                    ),
                )
                    ->by($this->contactRateLimitKey($request))
                    ->response(
                        static function (
                            Request $request,
                            array $headers,
                        ) use ($requestId): JsonResponse {
                            return response()->json(
                                [
                                    'success' => false,
                                    'message' => 'Too many contact submissions. Please try again later.',
                                    'request_id' => is_string($requestId)
                                        ? $requestId
                                        : null,
                                ],
                                Response::HTTP_TOO_MANY_REQUESTS,
                                $headers,
                            );
                        },
                    );
            },
        );
    }

    private function contactRateLimitKey(Request $request): string
    {
        $ipAddress = $request->ip();

        if ($ipAddress === null || $ipAddress === '') {
            return 'contact:unknown';
        }

        return 'contact:'.hash_hmac(
            'sha256',
            $ipAddress,
            (string) config('app.key'),
        );
    }
}
