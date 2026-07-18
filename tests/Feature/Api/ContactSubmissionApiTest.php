<?php

namespace Tests\Feature\Api;

use App\Contracts\Ai\AiAnalyzer;
use App\Contracts\Mail\ContactMailSender;
use App\Data\Ai\AiAnalysisResult;
use App\Enums\AiStatus;
use App\Enums\ContactRequestType;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Enums\Sentiment;
use App\Exceptions\AiAnalysisException;
use App\Mail\OwnerContactSubmissionMail;
use App\Mail\UserContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class ContactSubmissionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submission_is_normalized_analyzed_stored_and_returned_safely(): void
    {
        config()->set('ai.enabled', true);
        config()->set('contact.mail.enabled', false);

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
                ProcessingStatus::PartiallyCompleted->value,
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
            ProcessingStatus::PartiallyCompleted,
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
            MailStatus::Skipped,
            $submission->owner_mail_status,
        );
        $this->assertSame(
            MailStatus::Skipped,
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
        config()->set('contact.mail.enabled', false);

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
                ProcessingStatus::PartiallyCompleted->value,
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
            ProcessingStatus::PartiallyCompleted,
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

    public function test_unsafe_groq_auto_response_uses_safe_fallback(): void
    {
        config()->set('ai.enabled', true);
        config()->set('contact.mail.enabled', false);
        config()->set(
            'ai.providers.groq.base_url',
            'https://api.groq.test/openai/v1',
        );
        config()->set(
            'ai.providers.groq.api_key',
            'fake-api-key',
        );
        config()->set(
            'ai.providers.groq.model',
            'openai/gpt-oss-120b',
        );

        $this->app->forgetInstance(
            AiAnalyzer::class,
        );

        $unsafeAutoResponse = implode(' ', [
            'Здравствуйте!',
            'Мы свяжемся с вами скоро.',
        ]);

        Http::fake([
            'https://api.groq.test/openai/v1/*' => Http::response([
                'model' => 'openai/gpt-oss-120b',
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'sentiment' => 'positive',
                                'sentiment_score' => 0.7,
                                'request_type' => 'project_inquiry',
                                'auto_response' => $unsafeAutoResponse,
                            ], JSON_THROW_ON_ERROR),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 120,
                    'completion_tokens' => 40,
                    'total_tokens' => 160,
                ],
            ]),
        ]);

        $response = $this->postJson('/api/contact', [
            'name' => 'Сергей Волков',
            'phone' => '+7 700 444 55 66',
            'email' => 'sergey@example.com',
            'comment' => implode(' ', [
                'Здравствуйте!',
                'Нужна разработка Laravel API',
                'для внутреннего проекта.',
            ]),
        ]);

        $fallbackResponse = (string) config(
            'ai.fallback_response',
        );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.status',
                ProcessingStatus::PartiallyCompleted->value,
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

        Http::assertSentCount(1);

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
            $fallbackResponse,
            $submission->auto_response,
        );
        $this->assertNotSame(
            $unsafeAutoResponse,
            $submission->auto_response,
        );
        $this->assertNull(
            $submission->ai_prompt_tokens,
        );
        $this->assertNull(
            $submission->ai_completion_tokens,
        );
        $this->assertNull(
            $submission->ai_total_tokens,
        );
        $this->assertNotNull(
            $submission->ai_processed_at,
        );
    }

    public function test_disabled_ai_uses_fallback_without_calling_provider(): void
    {
        config()->set('ai.enabled', false);
        config()->set('contact.mail.enabled', false);

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

    public function test_successful_mail_delivery_completes_submission(): void
    {
        config()->set('ai.enabled', false);
        config()->set('contact.mail.enabled', true);
        config()->set(
            'contact.mail.owner_address',
            'owner@example.com',
        );

        Mail::fake();

        $response = $this->postJson('/api/contact', [
            'name' => 'Мария Иванова',
            'phone' => '+7 700 222 33 44',
            'email' => 'maria@example.com',
            'comment' => 'Хотела бы обсудить разработку Laravel API.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.status',
                ProcessingStatus::Completed->value,
            )
            ->assertJsonPath(
                'data.ai_status',
                AiStatus::Fallback->value,
            );

        $submission = ContactSubmission::query()
            ->firstOrFail();

        $this->assertSame(
            ProcessingStatus::Completed,
            $submission->processing_status,
        );
        $this->assertSame(
            MailStatus::Sent,
            $submission->owner_mail_status,
        );
        $this->assertSame(
            MailStatus::Sent,
            $submission->user_mail_status,
        );
        $this->assertNotNull(
            $submission->owner_mail_sent_at,
        );
        $this->assertNotNull(
            $submission->user_mail_sent_at,
        );
        $this->assertNotNull($submission->completed_at);

        Mail::assertSent(
            OwnerContactSubmissionMail::class,
            static fn (
                OwnerContactSubmissionMail $mail,
            ): bool => $mail->hasTo('owner@example.com'),
        );

        Mail::assertSent(
            UserContactSubmissionMail::class,
            static fn (
                UserContactSubmissionMail $mail,
            ): bool => $mail->hasTo('maria@example.com'),
        );

        Mail::assertSentCount(2);
    }

    public function test_owner_mail_failure_does_not_block_user_mail(): void
    {
        config()->set('ai.enabled', false);
        config()->set('contact.mail.enabled', true);
        config()->set(
            'contact.mail.owner_address',
            'owner@example.com',
        );

        $this->mock(
            ContactMailSender::class,
            function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('sendOwner')
                    ->once()
                    ->andThrow(
                        new RuntimeException(
                            'Simulated owner mail failure.',
                        ),
                    );

                $mock
                    ->shouldReceive('sendUser')
                    ->once();
            },
        );

        $response = $this->postJson('/api/contact', [
            'name' => 'Пётр Соколов',
            'phone' => '+7 700 333 44 55',
            'email' => 'petr@example.com',
            'comment' => 'Нужна консультация по Laravel-проекту.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.status',
                ProcessingStatus::PartiallyCompleted->value,
            );

        $submission = ContactSubmission::query()
            ->firstOrFail();

        $this->assertSame(
            ProcessingStatus::PartiallyCompleted,
            $submission->processing_status,
        );
        $this->assertSame(
            MailStatus::Failed,
            $submission->owner_mail_status,
        );
        $this->assertSame(
            MailStatus::Sent,
            $submission->user_mail_status,
        );
        $this->assertNull(
            $submission->owner_mail_sent_at,
        );
        $this->assertNotNull(
            $submission->user_mail_sent_at,
        );
        $this->assertNotNull($submission->completed_at);
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
