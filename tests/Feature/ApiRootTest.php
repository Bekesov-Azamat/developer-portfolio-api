<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRootTest extends TestCase
{
    public function test_api_root_returns_service_metadata(): void
    {
        $response = $this->getJson('/api');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'service' => config('app.name'),
                    'version' => config('app.version'),
                    'status' => 'operational',
                ],
            ]);
    }

    public function test_api_health_endpoint_reports_healthy_application(): void
    {
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
                ],
            ]);
    }
}
