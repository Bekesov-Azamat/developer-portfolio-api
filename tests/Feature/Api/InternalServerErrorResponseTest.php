<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class InternalServerErrorResponseTest extends TestCase
{
    public function test_unexpected_api_exception_returns_safe_traceable_json(): void
    {
        Route::get(
            '/api/testing/unexpected-error',
            static function (): never {
                throw new RuntimeException(
                    'Sensitive internal exception details.',
                );
            },
        );

        $response = $this->getJson(
            '/api/testing/unexpected-error',
        );

        $response
            ->assertInternalServerError()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Internal server error.',
            )
            ->assertJsonMissing([
                'exception',
                'file',
                'line',
                'trace',
            ]);

        $contents = $response->getContent();

        $this->assertIsString($contents);
        $this->assertStringNotContainsString(
            'Sensitive internal exception details.',
            $contents,
        );
        $this->assertStringNotContainsString(
            'RuntimeException',
            $contents,
        );

        $requestId = $response->headers->get(
            AssignRequestId::HEADER,
        );

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));
        $this->assertSame(
            $requestId,
            $response->json('request_id'),
        );
    }
}
