<?php

namespace Tests\Feature\Api;

use App\Contracts\Health\DatabaseHealthChecker;
use Mockery\MockInterface;
use Tests\TestCase;

class HealthApiTest extends TestCase
{
    public function test_health_endpoint_reports_healthy_database(): void
    {
        $this->mock(
            DatabaseHealthChecker::class,
            function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('isHealthy')
                    ->once()
                    ->andReturnTrue();
            },
        );

        $response = $this->getJson('/api/health');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'healthy',
                'version' => config('app.version'),
                'checks' => [
                    'application' => [
                        'ok' => true,
                    ],
                    'database' => [
                        'ok' => true,
                    ],
                ],
            ]);
    }

    public function test_health_endpoint_returns_503_when_database_is_unavailable(): void
    {
        $this->mock(
            DatabaseHealthChecker::class,
            function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('isHealthy')
                    ->once()
                    ->andReturnFalse();
            },
        );

        $response = $this->getJson('/api/health');

        $response
            ->assertServiceUnavailable()
            ->assertJson([
                'success' => false,
                'status' => 'unhealthy',
                'version' => config('app.version'),
                'checks' => [
                    'application' => [
                        'ok' => true,
                    ],
                    'database' => [
                        'ok' => false,
                    ],
                ],
            ]);

        $data = $response->json();

        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('exception', $data);
        $this->assertArrayNotHasKey('trace', $data);
        $this->assertArrayNotHasKey('database_url', $data);
    }
}
