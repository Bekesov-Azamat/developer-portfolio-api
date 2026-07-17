<?php

namespace App\Infrastructure\Ai;

use App\Contracts\Ai\AiAnalyzer;
use App\Data\Ai\AiAnalysisResult;
use App\Enums\ContactRequestType;
use App\Enums\Sentiment;
use App\Exceptions\AiAnalysisException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JsonException;

final readonly class GroqAiAnalyzer implements AiAnalyzer
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $model,
        private float $connectTimeout,
        private float $timeout,
        private int $maxOutputTokens,
        private float $temperature,
        private string $reasoningEffort,
    ) {}

    public function analyze(string $comment): AiAnalysisResult
    {
        $comment = trim($comment);

        if ($comment === '') {
            throw new AiAnalysisException(
                'Comment cannot be empty.',
            );
        }

        if ($this->apiKey === '') {
            throw new AiAnalysisException(
                'Groq API key is not configured.',
            );
        }

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->asJson()
                ->withToken($this->apiKey)
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->post(
                    'chat/completions',
                    $this->requestPayload($comment),
                )
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new AiAnalysisException(
                'Groq API request failed.',
                previous: $exception,
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new AiAnalysisException(
                'Groq returned an invalid response.',
            );
        }

        /** @var array<string, mixed> $payload */
        return $this->mapResponse($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(string $comment): array
    {
        return [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'reasoning_effort' => $this->reasoningEffort,
            'max_completion_tokens' => $this->maxOutputTokens,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => implode(' ', [
                        'Ты модуль анализа обращений',
                        'с сайта-портфолио разработчика.',
                        'Проанализируй только текст обращения.',
                        'Определи тональность, тип обращения',
                        'и подготовь вежливый автоответ на русском языке.',
                        'Автоответ должен содержать 2–4 коротких предложения,',
                        'быть профессиональным и не превышать 600 символов.',
                        'Отвечай от имени одного разработчика.',
                        'Используй первое лицо единственного числа.',
                        'Начинай автоответ строго с «Здравствуйте!».',
                        'Не используй приветствие «Привет».',
                        'Не используй местоимение «мы» и формы',
                        '«наш», «благодарим», «свяжемся».',
                        'Не указывай и не подразумевай сроки ответа,',
                        'включая «скоро» и «в ближайшее время».',
                        'Не выдумывай цены, сроки, опыт,',
                        'гарантии или договорённости.',
                        'Не повторяй персональные данные.',
                        'Не упоминай искусственный интеллект.',
                    ]),
                ],
                [
                    'role' => 'user',
                    'content' => $comment,
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'contact_submission_analysis',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'sentiment' => [
                                'type' => 'string',
                                'enum' => array_column(
                                    Sentiment::cases(),
                                    'value',
                                ),
                            ],
                            'sentiment_score' => [
                                'type' => 'number',
                                'minimum' => -1,
                                'maximum' => 1,
                            ],
                            'request_type' => [
                                'type' => 'string',
                                'enum' => array_column(
                                    ContactRequestType::cases(),
                                    'value',
                                ),
                            ],
                            'auto_response' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => [
                            'sentiment',
                            'sentiment_score',
                            'request_type',
                            'auto_response',
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapResponse(array $payload): AiAnalysisResult
    {
        $content = data_get(
            $payload,
            'choices.0.message.content',
        );

        if (! is_string($content) || trim($content) === '') {
            throw new AiAnalysisException(
                'Groq response content is missing.',
            );
        }

        try {
            $analysis = json_decode(
                $content,
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new AiAnalysisException(
                'Groq returned malformed JSON.',
                previous: $exception,
            );
        }

        if (! is_array($analysis)) {
            throw new AiAnalysisException(
                'Groq analysis has an invalid structure.',
            );
        }

        $sentiment = isset($analysis['sentiment'])
            && is_string($analysis['sentiment'])
                ? Sentiment::tryFrom($analysis['sentiment'])
                : null;

        $requestType = isset($analysis['request_type'])
            && is_string($analysis['request_type'])
                ? ContactRequestType::tryFrom(
                    $analysis['request_type'],
                )
                : null;

        $sentimentScore = $analysis['sentiment_score'] ?? null;
        $autoResponse = $analysis['auto_response'] ?? null;

        if (
            $sentiment === null
            || $requestType === null
            || ! is_int($sentimentScore)
                && ! is_float($sentimentScore)
            || ! is_string($autoResponse)
        ) {
            throw new AiAnalysisException(
                'Groq analysis contains invalid values.',
            );
        }

        $usage = $payload['usage'] ?? [];

        if (! is_array($usage)) {
            $usage = [];
        }

        $responseModel = $payload['model'] ?? null;

        try {
            return new AiAnalysisResult(
                sentiment: $sentiment,
                sentimentScore: (float) $sentimentScore,
                requestType: $requestType,
                autoResponse: trim($autoResponse),
                provider: 'groq',
                model: is_string($responseModel)
                    ? $responseModel
                    : $this->model,
                promptTokens: $this->optionalTokenCount(
                    $usage['prompt_tokens'] ?? null,
                ),
                completionTokens: $this->optionalTokenCount(
                    $usage['completion_tokens'] ?? null,
                ),
                totalTokens: $this->optionalTokenCount(
                    $usage['total_tokens'] ?? null,
                ),
            );
        } catch (InvalidArgumentException $exception) {
            throw new AiAnalysisException(
                'Groq analysis failed domain validation.',
                previous: $exception,
            );
        }
    }

    private function optionalTokenCount(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value) || $value < 0) {
            throw new AiAnalysisException(
                'Groq token usage is invalid.',
            );
        }

        return $value;
    }
}
