<?php

namespace Tests\Unit\Infrastructure\Ai;

use App\Enums\ContactRequestType;
use App\Enums\Sentiment;
use App\Exceptions\AiAnalysisException;
use App\Infrastructure\Ai\GroqAiAnalyzer;
use App\Services\Ai\AiAutoResponseValidator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqAiAnalyzerTest extends TestCase
{
    public function test_it_maps_structured_groq_response(): void
    {
        Http::fake([
            'https://api.groq.test/openai/v1/*' => Http::response([
                'model' => 'openai/gpt-oss-120b',
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'sentiment' => 'positive',
                                'sentiment_score' => 0.6,
                                'request_type' => 'project_inquiry',
                                'auto_response' => 'Здравствуйте! Спасибо за обращение. Я готов обсудить детали проекта.',
                            ], JSON_THROW_ON_ERROR),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 338,
                    'completion_tokens' => 127,
                    'total_tokens' => 465,
                ],
            ]),
        ]);

        $result = $this->analyzer()->analyze(
            'Хотим обсудить разработку Laravel-проекта.',
        );

        $this->assertSame(
            Sentiment::Positive,
            $result->sentiment,
        );
        $this->assertSame(0.6, $result->sentimentScore);
        $this->assertSame(
            ContactRequestType::ProjectInquiry,
            $result->requestType,
        );
        $this->assertSame('groq', $result->provider);
        $this->assertSame(
            'openai/gpt-oss-120b',
            $result->model,
        );
        $this->assertSame(338, $result->promptTokens);
        $this->assertSame(127, $result->completionTokens);
        $this->assertSame(465, $result->totalTokens);

        Http::assertSentCount(1);

        Http::assertSent(
            static function (Request $request): bool {
                $data = $request->data();

                return $request->url()
                    === 'https://api.groq.test/openai/v1/chat/completions'
                    && $request->hasHeader(
                        'Authorization',
                        'Bearer fake-api-key',
                    )
                    && ($data['model'] ?? null)
                        === 'openai/gpt-oss-120b'
                    && ($data['response_format']['type'] ?? null)
                        === 'json_schema';
            },
        );
    }

    public function test_it_requires_singular_voice_without_time_promises(): void
    {
        Http::fake([
            'https://api.groq.test/openai/v1/*' => Http::response([
                'model' => 'openai/gpt-oss-120b',
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'sentiment' => 'neutral',
                                'sentiment_score' => 0,
                                'request_type' => 'other',
                                'auto_response' => 'Здравствуйте! Спасибо за обращение. Я ознакомлюсь с сообщением.',
                            ], JSON_THROW_ON_ERROR),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->analyzer()->analyze(
            'Пользовательское обращение.',
        );

        Http::assertSent(
            static function (Request $request): bool {
                $messages = $request->data()['messages'] ?? null;

                if (! is_array($messages)) {
                    return false;
                }

                $systemPrompt = $messages[0]['content'] ?? null;

                return is_string($systemPrompt)
                    && str_contains(
                        $systemPrompt,
                        'Отвечай от имени одного разработчика.',
                    )
                    && str_contains(
                        $systemPrompt,
                        'Используй первое лицо единственного числа.',
                    )
                    && str_contains(
                        $systemPrompt,
                        'Начинай автоответ строго с «Здравствуйте!».',
                    )
                    && str_contains(
                        $systemPrompt,
                        'Не используй приветствие «Привет».',
                    )
                    && str_contains(
                        $systemPrompt,
                        'Не используй местоимение «мы»',
                    )
                    && str_contains(
                        $systemPrompt,
                        'Не указывай и не подразумевай сроки ответа',
                    );
            },
        );
    }

    public function test_it_rejects_unsafe_auto_response(): void
    {
        Http::fake([
            'https://api.groq.test/openai/v1/*' => Http::response([
                'model' => 'openai/gpt-oss-120b',
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'sentiment' => 'neutral',
                                'sentiment_score' => 0,
                                'request_type' => 'other',
                                'auto_response' => 'Здравствуйте! Мы свяжемся с вами скоро.',
                            ], JSON_THROW_ON_ERROR),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->expectException(AiAnalysisException::class);
        $this->expectExceptionMessage(
            'AI auto response uses plural developer voice.',
        );

        $this->analyzer()->analyze(
            'Тестовое обращение пользователя.',
        );
    }

    public function test_it_rejects_malformed_ai_json(): void
    {
        Http::fake([
            'https://api.groq.test/openai/v1/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'not-json',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->expectException(AiAnalysisException::class);
        $this->expectExceptionMessage(
            'Groq returned malformed JSON.',
        );

        $this->analyzer()->analyze(
            'Тестовое обращение пользователя.',
        );

        Http::assertSentCount(1);
    }

    private function analyzer(): GroqAiAnalyzer
    {
        return new GroqAiAnalyzer(
            baseUrl: 'https://api.groq.test/openai/v1',
            apiKey: 'fake-api-key',
            model: 'openai/gpt-oss-120b',
            connectTimeout: 2,
            timeout: 8,
            maxOutputTokens: 250,
            temperature: 0.2,
            reasoningEffort: 'low',
            autoResponseValidator: new AiAutoResponseValidator,
        );
    }
}
