<?php

namespace Tests\Feature\Models;

use App\Enums\AiStatus;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Models\ContactSubmission;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_request_id_and_uses_default_statuses(): void
    {
        $submission = ContactSubmission::query()->create([
            'name' => 'Azamat Bekesov',
            'phone' => '+77001234567',
            'email' => 'azamat@example.com',
            'comment' => 'I would like to discuss a Laravel project.',
        ])->refresh();

        $this->assertTrue(Str::isUuid($submission->request_id));
        $this->assertSame(ProcessingStatus::Pending, $submission->processing_status);
        $this->assertSame(AiStatus::NotAttempted, $submission->ai_status);
        $this->assertSame(MailStatus::NotAttempted, $submission->owner_mail_status);
        $this->assertSame(MailStatus::NotAttempted, $submission->user_mail_status);
    }

    public function test_request_id_cannot_be_overridden_through_mass_assignment(): void
    {
        $submittedRequestId = (string) Str::uuid();

        $submission = ContactSubmission::query()->create([
            'request_id' => $submittedRequestId,
            'name' => 'Azamat Bekesov',
            'phone' => '+77001234567',
            'email' => 'azamat@example.com',
            'comment' => 'Please contact me about backend development.',
        ]);

        $this->assertTrue(Str::isUuid($submission->request_id));
        $this->assertNotSame($submittedRequestId, $submission->request_id);
    }

    public function test_it_casts_statuses_and_processing_dates(): void
    {
        $processedAt = CarbonImmutable::parse('2026-07-17 18:00:00');

        $submission = ContactSubmission::factory()->create([
            'processing_status' => ProcessingStatus::Completed,
            'ai_status' => AiStatus::Succeeded,
            'owner_mail_status' => MailStatus::Sent,
            'user_mail_status' => MailStatus::Sent,
            'ai_processed_at' => $processedAt,
            'completed_at' => $processedAt,
        ])->refresh();

        $this->assertSame(ProcessingStatus::Completed, $submission->processing_status);
        $this->assertSame(AiStatus::Succeeded, $submission->ai_status);
        $this->assertSame(MailStatus::Sent, $submission->owner_mail_status);
        $this->assertSame(MailStatus::Sent, $submission->user_mail_status);
        $this->assertInstanceOf(CarbonImmutable::class, $submission->ai_processed_at);
        $this->assertInstanceOf(CarbonImmutable::class, $submission->completed_at);
        $this->assertTrue($processedAt->equalTo($submission->completed_at));
    }

    public function test_factory_creates_safe_technical_metadata(): void
    {
        $submission = ContactSubmission::factory()->create();

        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{64}$/',
            (string) $submission->ip_hash,
        );
        $this->assertNotEmpty($submission->user_agent);
    }
}
