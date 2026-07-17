<?php

namespace Tests\Feature\Api;

use App\Enums\AiStatus;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_endpoint_returns_safe_aggregated_statistics(): void
    {
        ContactSubmission::factory()->create([
            'name' => 'First Private User',
            'email' => 'first-private@example.com',
            'comment' => 'First private comment.',
            'processing_status' => ProcessingStatus::Completed,
            'ai_status' => AiStatus::Succeeded,
            'owner_mail_status' => MailStatus::Sent,
            'user_mail_status' => MailStatus::Sent,
            'ai_total_tokens' => 120,
        ]);

        ContactSubmission::factory()->create([
            'name' => 'Second Private User',
            'email' => 'second-private@example.com',
            'comment' => 'Second private comment.',
            'processing_status' => ProcessingStatus::PartiallyCompleted,
            'ai_status' => AiStatus::Fallback,
            'owner_mail_status' => MailStatus::Failed,
            'user_mail_status' => MailStatus::Sent,
            'ai_total_tokens' => null,
        ]);

        ContactSubmission::factory()->create([
            'processing_status' => ProcessingStatus::Processing,
            'ai_status' => AiStatus::Pending,
            'owner_mail_status' => MailStatus::Pending,
            'user_mail_status' => MailStatus::Pending,
            'ai_total_tokens' => 30,
        ]);

        $response = $this->getJson('/api/metrics');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_submissions', 3)
            ->assertJsonPath('data.processing.completed', 1)
            ->assertJsonPath(
                'data.processing.partially_completed',
                1,
            )
            ->assertJsonPath('data.processing.processing', 1)
            ->assertJsonPath('data.processing.pending', 0)
            ->assertJsonPath('data.processing.failed', 0)
            ->assertJsonPath('data.ai.succeeded', 1)
            ->assertJsonPath('data.ai.fallback', 1)
            ->assertJsonPath('data.ai.pending', 1)
            ->assertJsonPath('data.ai.failed', 0)
            ->assertJsonPath('data.ai.total_tokens', 150)
            ->assertJsonPath('data.mail.owner.sent', 1)
            ->assertJsonPath('data.mail.owner.failed', 1)
            ->assertJsonPath('data.mail.owner.pending', 1)
            ->assertJsonPath('data.mail.user.sent', 2)
            ->assertJsonPath('data.mail.user.pending', 1);

        $json = json_encode(
            $response->json(),
            JSON_THROW_ON_ERROR,
        );

        $this->assertStringNotContainsString(
            'first-private@example.com',
            $json,
        );
        $this->assertStringNotContainsString(
            'Second Private User',
            $json,
        );
        $this->assertStringNotContainsString(
            'private comment',
            $json,
        );
    }

    public function test_metrics_endpoint_returns_zeroed_structure_for_empty_database(): void
    {
        $response = $this->getJson('/api/metrics');

        $response
            ->assertOk()
            ->assertJsonPath('data.total_submissions', 0)
            ->assertJsonPath('data.processing.pending', 0)
            ->assertJsonPath('data.processing.completed', 0)
            ->assertJsonPath('data.ai.succeeded', 0)
            ->assertJsonPath('data.ai.fallback', 0)
            ->assertJsonPath('data.ai.total_tokens', 0)
            ->assertJsonPath('data.mail.owner.sent', 0)
            ->assertJsonPath('data.mail.user.sent', 0);
    }
}
