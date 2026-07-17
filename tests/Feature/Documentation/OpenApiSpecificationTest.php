<?php

namespace Tests\Feature\Documentation;

use App\Enums\AiStatus;
use App\Enums\ContactRequestType;
use App\Enums\ProcessingStatus;
use App\Enums\Sentiment;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use JsonException;
use RuntimeException;
use Tests\TestCase;

class OpenApiSpecificationTest extends TestCase
{
    public function test_openapi_document_matches_public_api_routes(): void
    {
        $specification = $this->specification();
        $paths = $this->paths($specification);

        $expectedRoutes = [
            'api.root' => [
                'uri' => 'api',
                'path' => '/api',
                'method' => 'GET',
            ],
            'contact.store' => [
                'uri' => 'api/contact',
                'path' => '/api/contact',
                'method' => 'POST',
            ],
            'api.health' => [
                'uri' => 'api/health',
                'path' => '/api/health',
                'method' => 'GET',
            ],
            'api.metrics' => [
                'uri' => 'api/metrics',
                'path' => '/api/metrics',
                'method' => 'GET',
            ],
        ];

        $documentedPaths = array_map(
            static fn (int|string $path): string => (string) $path,
            array_keys($paths),
        );

        sort($documentedPaths);

        $expectedPaths = array_column(
            $expectedRoutes,
            'path',
        );

        sort($expectedPaths);

        $this->assertSame(
            $expectedPaths,
            $documentedPaths,
            'OpenAPI contains missing or unexpected paths.',
        );

        foreach ($expectedRoutes as $routeName => $expected) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertInstanceOf(
                LaravelRoute::class,
                $route,
                "Laravel route {$routeName} is missing.",
            );

            $this->assertSame(
                $expected['uri'],
                $route->uri(),
            );

            $this->assertContains(
                $expected['method'],
                $route->methods(),
            );

            $pathItem = $paths[$expected['path']] ?? null;

            $this->assertIsArray(
                $pathItem,
                "OpenAPI path {$expected['path']} is missing.",
            );

            $operation = $pathItem[
                strtolower($expected['method'])
            ] ?? null;

            $this->assertIsArray(
                $operation,
                "OpenAPI operation {$expected['method']} "
                ."{$expected['path']} is missing.",
            );
        }
    }

    public function test_openapi_status_enums_match_domain_enums(): void
    {
        $specification = $this->specification();

        $this->assertSame(
            array_column(
                ProcessingStatus::cases(),
                'value',
            ),
            $this->schemaEnum(
                $specification,
                'ProcessingStatus',
            ),
        );

        $this->assertSame(
            array_column(
                AiStatus::cases(),
                'value',
            ),
            $this->schemaEnum(
                $specification,
                'AiStatus',
            ),
        );

        $this->assertSame(
            [
                ...array_column(
                    Sentiment::cases(),
                    'value',
                ),
                null,
            ],
            $this->schemaEnum(
                $specification,
                'Sentiment',
            ),
        );

        $this->assertSame(
            [
                ...array_column(
                    ContactRequestType::cases(),
                    'value',
                ),
                null,
            ],
            $this->schemaEnum(
                $specification,
                'ContactRequestType',
            ),
        );
    }

    public function test_openapi_version_matches_application_version(): void
    {
        $specification = $this->specification();

        $openApiVersion = $specification['openapi'] ?? null;
        $apiVersion = $specification['info']['version'] ?? null;

        $this->assertSame('3.1.0', $openApiVersion);
        $this->assertSame(
            config('app.version'),
            $apiVersion,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function specification(): array
    {
        $path = base_path('docs/openapi.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(
                'Unable to read docs/openapi.json.',
            );
        }

        try {
            $specification = json_decode(
                $contents,
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'docs/openapi.json contains invalid JSON.',
                previous: $exception,
            );
        }

        if (! is_array($specification)) {
            throw new RuntimeException(
                'OpenAPI root must be an object.',
            );
        }

        /** @var array<string, mixed> $specification */
        return $specification;
    }

    /**
     * @param  array<string, mixed>  $specification
     * @return array<string, mixed>
     */
    private function paths(array $specification): array
    {
        $paths = $specification['paths'] ?? null;

        if (! is_array($paths)) {
            throw new RuntimeException(
                'OpenAPI paths object is missing.',
            );
        }

        /** @var array<string, mixed> $paths */
        return $paths;
    }

    /**
     * @param  array<string, mixed>  $specification
     * @return list<mixed>
     */
    private function schemaEnum(
        array $specification,
        string $schema,
    ): array {
        $values = data_get(
            $specification,
            "components.schemas.{$schema}.enum",
        );

        if (! is_array($values)) {
            throw new RuntimeException(
                "OpenAPI enum {$schema} is missing.",
            );
        }

        return array_values($values);
    }
}
