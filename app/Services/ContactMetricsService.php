<?php

namespace App\Services;

use App\Contracts\Repositories\ContactMetricsRepository;
use App\Data\Metrics\ContactMetricsSnapshot;

final readonly class ContactMetricsService
{
    public function __construct(
        private ContactMetricsRepository $repository,
    ) {}

    public function snapshot(): ContactMetricsSnapshot
    {
        return $this->repository->snapshot();
    }
}
