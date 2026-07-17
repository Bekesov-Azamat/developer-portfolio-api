<?php

namespace App\Data\Ai;

use App\Enums\ContactRequestType;
use App\Enums\Sentiment;
use InvalidArgumentException;

final readonly class AiAnalysisResult
{
    public function __construct(
        public Sentiment $sentiment,
        public float $sentimentScore,
        public ContactRequestType $requestType,
        public string $autoResponse,
        public ?string $provider,
        public ?string $model,
        public ?int $promptTokens,
        public ?int $completionTokens,
        public ?int $totalTokens,
    ) {
        if ($sentimentScore < -1 || $sentimentScore > 1) {
            throw new InvalidArgumentException(
                'Sentiment score must be between -1 and 1.',
            );
        }

        if (trim($autoResponse) === '') {
            throw new InvalidArgumentException(
                'AI auto response cannot be empty.',
            );
        }

        if (mb_strlen($autoResponse) > 600) {
            throw new InvalidArgumentException(
                'AI auto response cannot exceed 600 characters.',
            );
        }
    }

    public static function fallback(
        string $autoResponse,
        ?string $provider = null,
        ?string $model = null,
    ): self {
        return new self(
            sentiment: Sentiment::Neutral,
            sentimentScore: 0.0,
            requestType: ContactRequestType::Other,
            autoResponse: $autoResponse,
            provider: $provider,
            model: $model,
            promptTokens: null,
            completionTokens: null,
            totalTokens: null,
        );
    }
}
