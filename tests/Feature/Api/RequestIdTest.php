<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestIdTest extends TestCase
{
    public function test_api_response_receives_generated_request_id(): void
    {
        $response = $this->getJson('/api');

        $response->assertOk();

        $requestId = $response->headers->get(AssignRequestId::HEADER);

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));
    }

    public function test_valid_incoming_request_id_is_preserved(): void
    {
        $requestId = (string) Str::uuid();

        $response = $this
            ->withHeader(AssignRequestId::HEADER, $requestId)
            ->getJson('/api');

        $response
            ->assertOk()
            ->assertHeader(AssignRequestId::HEADER, $requestId);
    }

    public function test_invalid_incoming_request_id_is_replaced(): void
    {
        $response = $this
            ->withHeader(AssignRequestId::HEADER, 'untrusted-value')
            ->getJson('/api');

        $response->assertOk();

        $requestId = $response->headers->get(AssignRequestId::HEADER);

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));
        $this->assertNotSame('untrusted-value', $requestId);
    }
}
