<?php

namespace App\Contracts\Health;

interface DatabaseHealthChecker
{
    public function isHealthy(): bool;
}
