<?php

namespace App\Data\Health;

final readonly class HealthCheckResult
{
    public function __construct(
        public bool $databaseHealthy,
    ) {}

    public function isHealthy(): bool
    {
        return $this->databaseHealthy;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isHealthy(),
            'status' => $this->isHealthy()
                ? 'healthy'
                : 'unhealthy',
            'version' => config('app.version'),
            'checks' => [
                'application' => [
                    'ok' => true,
                ],
                'database' => [
                    'ok' => $this->databaseHealthy,
                ],
            ],
        ];
    }
}
