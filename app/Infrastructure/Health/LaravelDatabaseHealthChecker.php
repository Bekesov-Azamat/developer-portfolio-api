<?php

namespace App\Infrastructure\Health;

use App\Contracts\Health\DatabaseHealthChecker;
use Illuminate\Support\Facades\DB;
use Throwable;

final class LaravelDatabaseHealthChecker implements DatabaseHealthChecker
{
    public function isHealthy(): bool
    {
        try {
            DB::connection()->select('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
