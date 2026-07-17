<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApiRequestLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_request_is_logged_without_personal_data(): void
    {
        $logPath = storage_path(
            'framework/testing/api-requests.log',
        );

        File::ensureDirectoryExists(dirname($logPath));
        File::delete($logPath);

        config()->set('logging.channels.api_requests', [
            'driver' => 'single',
            'path' => $logPath,
            'level' => 'info',
            'replace_placeholders' => true,
        ]);

        try {
            $response = $this->postJson('/api/contact', [
                'name' => 'Private Test User',
                'phone' => '+77009998877',
                'email' => 'private@example.com',
                'comment' => 'This confidential comment must not be logged.',
            ]);

            $response->assertCreated();

            $requestId = $response->headers->get(
                AssignRequestId::HEADER,
            );

            $this->assertIsString($requestId);
            $this->assertFileExists($logPath);

            $contents = File::get($logPath);

            $this->assertStringContainsString(
                'api_request',
                $contents,
            );
            $this->assertStringContainsString(
                $requestId,
                $contents,
            );
            $this->assertStringContainsString(
                'contact.store',
                $contents,
            );
            $this->assertStringContainsString(
                '201',
                $contents,
            );

            $this->assertStringNotContainsString(
                'Private Test User',
                $contents,
            );
            $this->assertStringNotContainsString(
                '+77009998877',
                $contents,
            );
            $this->assertStringNotContainsString(
                'private@example.com',
                $contents,
            );
            $this->assertStringNotContainsString(
                'This confidential comment must not be logged.',
                $contents,
            );
        } finally {
            File::delete($logPath);
        }
    }
}
