<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiErrorResponseTest extends TestCase
{
    public function test_unknown_api_endpoint_returns_traceable_json_error(): void
    {
        $response = $this->getJson('/api/unknown-endpoint');

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'API endpoint not found.');

        $this->assertTraceableErrorResponse($response);
    }

    public function test_unsupported_http_method_returns_traceable_json_error(): void
    {
        $response = $this->putJson('/api/contact', []);

        $response
            ->assertStatus(405)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'HTTP method not allowed.');

        $this->assertTraceableErrorResponse($response);
    }

    private function assertTraceableErrorResponse(mixed $response): void
    {
        $requestId = $response->headers->get(
            AssignRequestId::HEADER,
        );

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));
        $this->assertSame(
            $requestId,
            $response->json('request_id'),
        );
    }
}
