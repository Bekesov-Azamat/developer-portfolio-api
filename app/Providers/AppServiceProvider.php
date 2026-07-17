<?php

namespace App\Providers;

use App\Contracts\Ai\AiAnalyzer;
use App\Contracts\Mail\ContactMailSender;
use App\Contracts\Repositories\ContactSubmissionRepository;
use App\Http\Middleware\AssignRequestId;
use App\Infrastructure\Ai\GroqAiAnalyzer;
use App\Infrastructure\Mail\LaravelContactMailSender;
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

        $this->app->bind(
            ContactMailSender::class,
            LaravelContactMailSender::class,
        );

        $this->app->singleton(
            AiAnalyzer::class,
            static fn (): GroqAiAnalyzer => new GroqAiAnalyzer(
                baseUrl: (string) config(
                    'ai.providers.groq.base_url',
                ),
                apiKey: (string) config(
                    'ai.providers.groq.api_key',
                ),
                model: (string) config(
                    'ai.providers.groq.model',
                ),
                connectTimeout: (float) config(
                    'ai.providers.groq.connect_timeout',
                    2,
                ),
                timeout: (float) config(
                    'ai.providers.groq.timeout',
                    8,
                ),
                maxOutputTokens: max(
                    1,
                    (int) config(
                        'ai.providers.groq.max_output_tokens',
                        250,
                    ),
                ),
                temperature: (float) config(
                    'ai.providers.groq.temperature',
                    0.2,
                ),
                reasoningEffort: (string) config(
                    'ai.providers.groq.reasoning_effort',
                    'low',
                ),
            ),
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
                        (int) config(
                            'contact.rate_limit.per_minute',
                            5,
                        ),
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
