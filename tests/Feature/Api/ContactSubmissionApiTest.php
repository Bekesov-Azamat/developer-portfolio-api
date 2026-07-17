<?php

namespace Tests\Feature\Api;

use App\Contracts\Ai\AiAnalyzer;
use App\Data\Ai\AiAnalysisResult;
use App\Enums\AiStatus;
use App\Enums\ContactRequestType;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Enums\Sentiment;
use App\Exceptions\AiAnalysisException;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class ContactSubmissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submission_is_normalized_analyzed_stored_and_returned_safely(): void
    {
        config()->set('ai.enabled', true);

        $comment = 'I would like to discuss a Laravel backend project.';
        $autoResponse = 'Спасибо за обращение! Готов обсудить детали проекта.';

        $this->mock(
            AiAnalyzer::class,
            function (MockInterface $mock) use (
                $comment,
                $autoResponse,
            ): void {
                $mock
                    ->shouldReceive('analyze')
                    ->once()
                    ->with($comment)
                    ->andReturn(
                        new AiAnalysisResult(
                            sentiment: Sentiment::Positive,
                            sentimentScore: 0.6,
                            requestType: ContactRequestType::ProjectInquiry,
                            autoResponse: $autoResponse,
                            provider: 'groq',
                            model: 'openai/gpt-oss-120b',
                            promptTokens: 338,
                            completionTokens: 127,
                            totalTokens: 465,
                        ),
                    );
            },
        );

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
                'comment' => "  {$comment}  ",
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                'Contact submission accepted.',
            )
            ->assertJsonPath(
                'data.status',
                ProcessingStatus::Processing->value,
            )
            ->assertJsonPath(
                'data.ai_status',
                AiStatus::Succeeded->value,
            )
            ->assertJsonPath(
                'data.analysis.sentiment',
                Sentiment::Positive->value,
            )
            ->assertJsonPath(
                'data.analysis.sentiment_score',
                0.6,
            )
            ->assertJsonPath(
                'data.analysis.request_type',
                ContactRequestType::ProjectInquiry->value,
            )
            ->assertJsonPath(
                'data.auto_response',
                $autoResponse,
            )
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'request_id',
                    'status',
                    'ai_status',
                    'analysis' => [
                        'sentiment',
                        'sentiment_score',
                        'request_type',
                    ],
                    'auto_response',
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
        $this->assertArrayNotHasKey('ai_provider', $responseData);
        $this->assertArrayNotHasKey('ai_model', $responseData);
        $this->assertArrayNotHasKey('ai_total_tokens', $responseData);

        $submission = ContactSubmission::query()
            ->where('request_id', $requestId)
            ->firstOrFail();

        $this->assertSame('Azamat Bekesov', $submission->name);
        $this->assertSame(
            '+7 (700) 123-45-67',
            $submission->phone,
        );
        $this->assertSame(
            'azamat@example.com',
            $submission->email,
        );
        $this->assertSame($comment, $submission->comment);

        $this->assertSame(
            ProcessingStatus::Processing,
            $submission->processing_status,
        );
        $this->assertSame(
            AiStatus::Succeeded,
            $submission->ai_status,
        );
        $this->assertSame(
            Sentiment::Positive,
            $submission->sentiment,
        );
        $this->assertSame(
            ContactRequestType::ProjectInquiry,
            $submission->request_type,
        );
        $this->assertSame(
            '0.6000',
            $submission->sentiment_score,
        );
        $this->assertSame(
            $autoResponse,
            $submission->auto_response,
        );
        $this->assertSame('groq', $submission->ai_provider);
        $this->assertSame(
            'openai/gpt-oss-120b',
            $submission->ai_model,
        );
        $this->assertSame(338, $submission->ai_prompt_tokens);
        $this->assertSame(
            127,
            $submission->ai_completion_tokens,
        );
        $this->assertSame(465, $submission->ai_total_tokens);
        $this->assertNotNull($submission->ai_processed_at);

        $this->assertSame(
            MailStatus::NotAttempted,
            $submission->owner_mail_status,
        );
        $this->assertSame(
            MailStatus::NotAttempted,
            $submission->user_mail_status,
        );

        $this->assertSame(
            hash_hmac(
                'sha256',
                $ipAddress,
                (string) config('app.key'),
            ),
            $submission->ip_hash,
        );
        $this->assertSame(
            $userAgent,
            $submission->user_agent,
        );
    }

    public function test_ai_failure_uses_safe_fallback_and_keeps_submission(): void
    {
        config()->set('ai.enabled', true);

        $comment = 'Нужно обсудить разработку внутреннего сервиса.';

        $this->mock(
            AiAnalyzer::class,
            function (MockInterface $mock) use ($comment): void {
                $mock
                    ->shouldReceive('analyze')
                    ->once()
                    ->with($comment)
                    ->andThrow(
                        new AiAnalysisException(
                            'Simulated Groq failure.',
                        ),
                    );
            },
        );

        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'phone' => '+7 700 555 44 33',
            'email' => 'ivan@example.com',
            'comment' => $comment,
        ]);

        $fallbackResponse = (string) config(
            'ai.fallback_response',
        );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.status',
                ProcessingStatus::Processing->value,
            )
            ->assertJsonPath(
                'data.ai_status',
                AiStatus::Fallback->value,
            )
            ->assertJsonPath(
                'data.analysis.sentiment',
                Sentiment::Neutral->value,
            )
            ->assertJsonPath(
                'data.analysis.request_type',
                ContactRequestType::Other->value,
            )
            ->assertJsonPath(
                'data.auto_response',
                $fallbackResponse,
            );

        $this->assertSame(
            0.0,
            (float) $response->json(
                'data.analysis.sentiment_score',
            ),
        );

        $this->assertDatabaseCount(
            'contact_submissions',
            1,
        );

        $submission = ContactSubmission::query()
            ->firstOrFail();

        $this->assertSame($comment, $submission->comment);
        $this->assertSame(
            ProcessingStatus::Processing,
            $submission->processing_status,
        );
        $this->assertSame(
            AiStatus::Fallback,
            $submission->ai_status,
        );
        $this->assertSame(
            Sentiment::Neutral,
            $submission->sentiment,
        );
        $this->assertSame(
            ContactRequestType::Other,
            $submission->request_type,
        );
        $this->assertSame(
            '0.0000',
            $submission->sentiment_score,
        );
        $this->assertSame(
            $fallbackResponse,
            $submission->auto_response,
        );
        $this->assertSame('groq', $submission->ai_provider);
        $this->assertSame(
            'openai/gpt-oss-120b',
            $submission->ai_model,
        );
        $this->assertNull($submission->ai_prompt_tokens);
        $this->assertNull(
            $submission->ai_completion_tokens,
        );
        $this->assertNull($submission->ai_total_tokens);
        $this->assertNotNull($submission->ai_processed_at);
    }

    public function test_disabled_ai_uses_fallback_without_calling_provider(): void
    {
        config()->set('ai.enabled', false);

        $this->mock(
            AiAnalyzer::class,
            function (MockInterface $mock): void {
                $mock->shouldNotReceive('analyze');
            },
        );

        $response = $this->postJson('/api/contact', [
            'name' => 'Алексей Смирнов',
            'phone' => '+7 700 111 22 33',
            'email' => 'alexey@example.com',
            'comment' => 'Хотел бы обсудить разработку нового API-сервиса.',
        ]);

        $fallbackResponse = (string) config(
            'ai.fallback_response',
        );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.ai_status',
                AiStatus::Fallback->value,
            )
            ->assertJsonPath(
                'data.analysis.sentiment',
                Sentiment::Neutral->value,
            )
            ->assertJsonPath(
                'data.analysis.sentiment_score',
                0,
            )
            ->assertJsonPath(
                'data.analysis.request_type',
                ContactRequestType::Other->value,
            )
            ->assertJsonPath(
                'data.auto_response',
                $fallbackResponse,
            );

        $this->assertDatabaseCount(
            'contact_submissions',
            1,
        );

        $submission = ContactSubmission::query()
            ->firstOrFail();

        $this->assertSame(
            AiStatus::Fallback,
            $submission->ai_status,
        );
        $this->assertSame(
            Sentiment::Neutral,
            $submission->sentiment,
        );
        $this->assertSame(
            ContactRequestType::Other,
            $submission->request_type,
        );
        $this->assertNull($submission->ai_prompt_tokens);
        $this->assertNull(
            $submission->ai_completion_tokens,
        );
        $this->assertNull($submission->ai_total_tokens);
        $this->assertNotNull($submission->ai_processed_at);
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
            ->assertJsonPath(
                'message',
                'Validation failed.',
            )
            ->assertJsonValidationErrors([
                'name',
                'phone',
                'email',
                'comment',
            ]);

        $this->assertDatabaseCount(
            'contact_submissions',
            0,
        );
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

        $this->assertDatabaseCount(
            'contact_submissions',
            0,
        );
    }
}
