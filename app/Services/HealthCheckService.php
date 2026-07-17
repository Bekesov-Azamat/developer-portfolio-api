<?php

namespace App\Services;

use App\Contracts\Health\DatabaseHealthChecker;
use App\Data\Health\HealthCheckResult;

final readonly class HealthCheckService
{
    public function __construct(
        private DatabaseHealthChecker $database,
    ) {}

    public function check(): HealthCheckResult
    {
        return new HealthCheckResult(
            databaseHealthy: $this->database->isHealthy(),
        );
    }
}
