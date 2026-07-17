<?php

namespace Tests\Feature\Api;

use App\Enums\AiStatus;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactSubmissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submission_is_normalized_stored_and_returned_safely(): void
    {
        $ipAddress = '203.0.113.10';
        $userAgent = 'Developer Portfolio API Test Client';

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => $ipAddress,
            ])
            ->withHeader('User-Agent', $userAgent)
            ->postJson('/api/contact', [
                'name' => '  Azamat    Bekesov  ',
                'phone' => '  +7 (700) 123-45-67  ',
                'email' => '  AZAMAT@EXAMPLE.COM  ',
                'comment' => '  I would like to discuss a Laravel backend project.  ',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Contact submission accepted.')
            ->assertJsonPath('data.status', ProcessingStatus::Pending->value)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'request_id',
                    'status',
                    'submitted_at',
                ],
            ]);

        $requestId = $response->json('data.request_id');

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));

        $responseData = $response->json('data');

        $this->assertIsArray($responseData);
        $this->assertArrayNotHasKey('name', $responseData);
        $this->assertArrayNotHasKey('phone', $responseData);
        $this->assertArrayNotHasKey('email', $responseData);
        $this->assertArrayNotHasKey('comment', $responseData);
        $this->assertArrayNotHasKey('ip_hash', $responseData);
        $this->assertArrayNotHasKey('user_agent', $responseData);

        $submission = ContactSubmission::query()
            ->where('request_id', $requestId)
            ->firstOrFail();

        $this->assertSame('Azamat Bekesov', $submission->name);
        $this->assertSame('+7 (700) 123-45-67', $submission->phone);
        $this->assertSame('azamat@example.com', $submission->email);
        $this->assertSame(
            'I would like to discuss a Laravel backend project.',
            $submission->comment,
        );

        $this->assertSame(ProcessingStatus::Pending, $submission->processing_status);
        $this->assertSame(AiStatus::NotAttempted, $submission->ai_status);
        $this->assertSame(MailStatus::NotAttempted, $submission->owner_mail_status);
        $this->assertSame(MailStatus::NotAttempted, $submission->user_mail_status);

        $this->assertSame(
            hash_hmac('sha256', $ipAddress, (string) config('app.key')),
            $submission->ip_hash,
        );
        $this->assertSame($userAgent, $submission->user_agent);
    }

    public function test_invalid_contact_submission_returns_json_validation_errors(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'A',
            'phone' => 'invalid phone!',
            'email' => 'not-an-email',
            'comment' => 'short',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors([
                'name',
                'phone',
                'email',
                'comment',
            ]);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_required_contact_fields_return_json_validation_errors(): void
    {
        $response = $this->postJson('/api/contact');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'phone',
                'email',
                'comment',
            ]);

        $this->assertDatabaseCount('contact_submissions', 0);
    }
}
