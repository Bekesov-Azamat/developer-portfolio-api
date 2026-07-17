<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_allowed_origin_receives_preflight_headers(): void
    {
        $origin = 'http://localhost:5173';

        $response = $this->call(
            method: 'OPTIONS',
            uri: '/api/contact',
            server: [
                'HTTP_ORIGIN' => $origin,
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, X-Request-ID',
            ],
        );

        $response
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', $origin);

        $allowedMethods = $response->headers->get(
            'Access-Control-Allow-Methods',
        );

        $allowedHeaders = $response->headers->get(
            'Access-Control-Allow-Headers',
        );

        $this->assertIsString($allowedMethods);
        $this->assertStringContainsString('POST', $allowedMethods);

        $this->assertIsString($allowedHeaders);
        $this->assertStringContainsString(
            'content-type',
            strtolower($allowedHeaders),
        );
        $this->assertStringContainsString(
            'x-request-id',
            strtolower($allowedHeaders),
        );
    }

    public function test_disallowed_origin_receives_no_cors_permission(): void
    {
        $response = $this->call(
            method: 'OPTIONS',
            uri: '/api/contact',
            server: [
                'HTTP_ORIGIN' => 'https://untrusted.example',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            ],
        );

        $response
            ->assertNoContent()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_actual_api_response_exposes_request_id_header(): void
    {
        $origin = 'http://localhost:5173';

        $response = $this
            ->withHeader('Origin', $origin)
            ->getJson('/api');

        $response
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', $origin);

        $requestId = $response->headers->get(
            AssignRequestId::HEADER,
        );

        $exposedHeaders = $response->headers->get(
            'Access-Control-Expose-Headers',
        );

        $this->assertIsString($requestId);
        $this->assertIsString($exposedHeaders);
        $this->assertStringContainsString(
            strtolower(AssignRequestId::HEADER),
            strtolower($exposedHeaders),
        );
    }
}
