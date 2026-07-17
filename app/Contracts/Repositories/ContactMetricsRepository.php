<?php

namespace App\Contracts\Repositories;

use App\Data\Metrics\ContactMetricsSnapshot;

interface ContactMetricsRepository
{
    public function snapshot(): ContactMetricsSnapshot;
}
