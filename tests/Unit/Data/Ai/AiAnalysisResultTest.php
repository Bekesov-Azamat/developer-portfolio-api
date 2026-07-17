<?php

namespace Tests\Unit\Data\Ai;

use App\Data\Ai\AiAnalysisResult;
use App\Enums\ContactRequestType;
use App\Enums\Sentiment;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AiAnalysisResultTest extends TestCase
{
    public function test_it_creates_safe_fallback_result(): void
    {
        $result = AiAnalysisResult::fallback(
            autoResponse: 'Спасибо за обращение! Ваше сообщение получено.',
            provider: 'groq',
            model: 'openai/gpt-oss-120b',
        );

        $this->assertSame(Sentiment::Neutral, $result->sentiment);
        $this->assertSame(0.0, $result->sentimentScore);
        $this->assertSame(
            ContactRequestType::Other,
            $result->requestType,
        );
        $this->assertSame('groq', $result->provider);
        $this->assertSame(
            'openai/gpt-oss-120b',
            $result->model,
        );
        $this->assertNull($result->promptTokens);
        $this->assertNull($result->completionTokens);
        $this->assertNull($result->totalTokens);
    }

    public function test_it_rejects_score_outside_allowed_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Sentiment score must be between -1 and 1.',
        );

        new AiAnalysisResult(
            sentiment: Sentiment::Positive,
            sentimentScore: 1.1,
            requestType: ContactRequestType::Feedback,
            autoResponse: 'Спасибо за положительный отзыв!',
            provider: 'groq',
            model: 'openai/gpt-oss-120b',
            promptTokens: 100,
            completionTokens: 50,
            totalTokens: 150,
        );
    }
}
