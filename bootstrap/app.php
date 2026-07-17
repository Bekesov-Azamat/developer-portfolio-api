<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\LogApiRequest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$apiErrorResponse = static function (
    Request $request,
    string $message,
    int $status,
    array $errors = [],
): JsonResponse {
    $requestId = $request->attributes->get(
        AssignRequestId::ATTRIBUTE,
    );

    if (! is_string($requestId) || ! Str::isUuid($requestId)) {
        $requestId = (string) Str::uuid();

        $request->attributes->set(
            AssignRequestId::ATTRIBUTE,
            $requestId,
        );
    }

    $payload = [
        'success' => false,
        'message' => $message,
        'request_id' => $requestId,
    ];

    if ($errors !== []) {
        $payload['errors'] = $errors;
    }

    $response = response()->json($payload, $status);
    $response->headers->set(AssignRequestId::HEADER, $requestId);

    return $response;
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AssignRequestId::class);
        $middleware->append(LogApiRequest::class);

        $middleware->prependToPriorityList(
            before: ThrottleRequests::class,
            prepend: AssignRequestId::class,
        );
    })
    ->withExceptions(
        function (Exceptions $exceptions) use ($apiErrorResponse): void {
            $exceptions->shouldRenderJsonWhen(
                static fn (Request $request): bool => $request->is('api/*'),
            );

            $exceptions->render(
                static function (
                    ValidationException $exception,
                    Request $request,
                ) use ($apiErrorResponse): ?JsonResponse {
                    if (! $request->is('api/*')) {
                        return null;
                    }

                    return $apiErrorResponse(
                        request: $request,
                        message: 'Validation failed.',
                        status: 422,
                        errors: $exception->errors(),
                    );
                },
            );

            $exceptions->render(
                static function (
                    NotFoundHttpException $exception,
                    Request $request,
                ) use ($apiErrorResponse): ?JsonResponse {
                    if (! $request->is('api/*')) {
                        return null;
                    }

                    return $apiErrorResponse(
                        request: $request,
                        message: 'API endpoint not found.',
                        status: 404,
                    );
                },
            );

            $exceptions->render(
                static function (
                    MethodNotAllowedHttpException $exception,
                    Request $request,
                ) use ($apiErrorResponse): ?JsonResponse {
                    if (! $request->is('api/*')) {
                        return null;
                    }

                    return $apiErrorResponse(
                        request: $request,
                        message: 'HTTP method not allowed.',
                        status: 405,
                    );
                },
            );

            $exceptions->render(
                static function (
                    Throwable $exception,
                    Request $request,
                ) use ($apiErrorResponse): ?JsonResponse {
                    if (
                        ! $request->is('api/*')
                        || $exception instanceof HttpExceptionInterface
                        || $exception instanceof HttpResponseException
                    ) {
                        return null;
                    }

                    return $apiErrorResponse(
                        request: $request,
                        message: 'Internal server error.',
                        status: 500,
                    );
                },
            );
        },
    )
    ->create();
