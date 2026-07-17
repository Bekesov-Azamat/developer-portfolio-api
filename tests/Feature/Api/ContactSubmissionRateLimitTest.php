<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactSubmissionRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submissions_are_rate_limited_by_ip_address(): void
    {
        config()->set('contact.rate_limit.per_minute', 2);

        $ipAddress = '198.51.100.77';

        $payload = [
            'name' => 'Azamat Bekesov',
            'phone' => '+77001234567',
            'email' => 'azamat@example.com',
            'comment' => 'I would like to discuss a Laravel backend project.',
        ];

        $this
            ->withServerVariables(['REMOTE_ADDR' => $ipAddress])
            ->postJson('/api/contact', $payload)
            ->assertCreated();

        $this
            ->withServerVariables(['REMOTE_ADDR' => $ipAddress])
            ->postJson('/api/contact', $payload)
            ->assertCreated();

        $limitedResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => $ipAddress])
            ->postJson('/api/contact', $payload);

        $limitedResponse
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Too many contact submissions. Please try again later.',
            );

        $requestId = $limitedResponse->headers->get(
            AssignRequestId::HEADER,
        );

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));
        $this->assertSame(
            $requestId,
            $limitedResponse->json('request_id'),
        );

        $this->assertNotNull(
            $limitedResponse->headers->get('Retry-After'),
        );

        $this->assertDatabaseCount('contact_submissions', 2);
    }
}
