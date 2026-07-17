<?php

namespace App\Data\Metrics;

final readonly class ContactMetricsSnapshot
{
    /**
     * @param  array<string, int>  $processing
     * @param  array<string, int>  $ai
     * @param  array<string, int>  $ownerMail
     * @param  array<string, int>  $userMail
     */
    public function __construct(
        public int $totalSubmissions,
        public array $processing,
        public array $ai,
        public array $ownerMail,
        public array $userMail,
        public int $aiTotalTokens,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_submissions' => $this->totalSubmissions,
            'processing' => $this->processing,
            'ai' => [
                ...$this->ai,
                'total_tokens' => $this->aiTotalTokens,
            ],
            'mail' => [
                'owner' => $this->ownerMail,
                'user' => $this->userMail,
            ],
        ];
    }
}
