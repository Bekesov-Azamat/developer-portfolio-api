<?php

namespace Tests\Feature\Documentation;

use JsonException;
use RuntimeException;
use Tests\TestCase;

class PostmanCollectionTest extends TestCase
{
    public function test_collection_contains_expected_requests(): void
    {
        $collection = $this->collection();

        $this->assertSame(
            'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            data_get($collection, 'info.schema'),
        );

        $this->assertSame(
            'http://localhost:8020',
            $this->variableValue(
                $collection,
                'base_url',
            ),
        );

        $requests = $this->requests($collection);

        $actual = [];

        foreach ($requests as $request) {
            $actual[] = [
                $request['name'],
                $request['method'],
                $request['url'],
            ];
        }

        $this->assertSame([
            [
                'Get service metadata',
                'GET',
                '{{base_url}}/api',
            ],
            [
                'Check application health',
                'GET',
                '{{base_url}}/api/health',
            ],
            [
                'Get aggregate metrics',
                'GET',
                '{{base_url}}/api/metrics',
            ],
            [
                'Submit valid contact request',
                'POST',
                '{{base_url}}/api/contact',
            ],
            [
                'Reject invalid contact request',
                'POST',
                '{{base_url}}/api/contact',
            ],
        ], $actual);
    }

    public function test_valid_contact_request_checks_complete_workflow(): void
    {
        $request = $this->requestByName(
            $this->collection(),
            'Submit valid contact request',
        );

        $this->assertStringContainsString(
            '{{$timestamp}}',
            $request['body'],
        );

        $this->assertStringContainsString(
            'pm.response.to.have.status(201)',
            $request['tests'],
        );

        $this->assertStringContainsString(
            'json.data.ai_status',
            $request['tests'],
        );

        $this->assertStringContainsString(
            'json.data.analysis.sentiment',
            $request['tests'],
        );

        $this->assertStringContainsString(
            'submission_request_id',
            $request['tests'],
        );
    }

    public function test_collection_contains_no_hardcoded_secrets(): void
    {
        $path = base_path(
            'docs/postman/'
            .'developer-portfolio-api.postman_collection.json',
        );

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(
                'Unable to read Postman Collection.',
            );
        }

        $this->assertStringNotContainsString(
            'gsk_',
            $contents,
        );

        $this->assertStringNotContainsString(
            'AI_API_KEY',
            $contents,
        );

        $this->assertStringNotContainsString(
            'MAIL_PASSWORD',
            $contents,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function collection(): array
    {
        $path = base_path(
            'docs/postman/'
            .'developer-portfolio-api.postman_collection.json',
        );

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(
                'Unable to read Postman Collection.',
            );
        }

        try {
            $collection = json_decode(
                $contents,
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Postman Collection contains invalid JSON.',
                previous: $exception,
            );
        }

        if (! is_array($collection)) {
            throw new RuntimeException(
                'Postman Collection root must be an object.',
            );
        }

        /** @var array<string, mixed> $collection */
        return $collection;
    }

    /**
     * @param  array<string, mixed>  $collection
     */
    private function variableValue(
        array $collection,
        string $key,
    ): ?string {
        $variables = $collection['variable'] ?? [];

        if (! is_array($variables)) {
            return null;
        }

        foreach ($variables as $variable) {
            if (
                ! is_array($variable)
                || ($variable['key'] ?? null) !== $key
            ) {
                continue;
            }

            $value = $variable['value'] ?? null;

            return is_string($value) ? $value : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $collection
     * @return list<array{
     *     name: string,
     *     method: string,
     *     url: string,
     *     body: string,
     *     tests: string
     * }>
     */
    private function requests(array $collection): array
    {
        $requests = [];
        $items = $collection['item'] ?? [];

        if (is_array($items)) {
            $this->collectRequests(
                $items,
                $requests,
            );
        }

        return $requests;
    }

    /**
     * @param  array<int|string, mixed>  $items
     * @param  list<array{
     *     name: string,
     *     method: string,
     *     url: string,
     *     body: string,
     *     tests: string
     * }>  $requests
     */
    private function collectRequests(
        array $items,
        array &$requests,
    ): void {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $request = $item['request'] ?? null;

            if (is_array($request)) {
                $url = $request['url'] ?? null;
                $rawUrl = is_array($url)
                    ? ($url['raw'] ?? '')
                    : '';

                $body = data_get(
                    $request,
                    'body.raw',
                    '',
                );

                $requests[] = [
                    'name' => is_string($item['name'] ?? null)
                        ? $item['name']
                        : '',
                    'method' => is_string(
                        $request['method'] ?? null,
                    )
                        ? $request['method']
                        : '',
                    'url' => is_string($rawUrl)
                        ? $rawUrl
                        : '',
                    'body' => is_string($body)
                        ? $body
                        : '',
                    'tests' => $this->testScripts($item),
                ];
            }

            $children = $item['item'] ?? null;

            if (is_array($children)) {
                $this->collectRequests(
                    $children,
                    $requests,
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function test_scripts(array $item): string
    {
        $events = $item['event'] ?? [];

        if (! is_array($events)) {
            return '';
        }

        $lines = [];

        foreach ($events as $event) {
            if (
                ! is_array($event)
                || ($event['listen'] ?? null) !== 'test'
            ) {
                continue;
            }

            $script = data_get(
                $event,
                'script.exec',
                [],
            );

            if (! is_array($script)) {
                continue;
            }

            foreach ($script as $line) {
                if (is_string($line)) {
                    $lines[] = $line;
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $collection
     * @return array{
     *     name: string,
     *     method: string,
     *     url: string,
     *     body: string,
     *     tests: string
     * }
     */
    /**
     * @param  array<string, mixed>  $item
     */
    private function testScripts(array $item): string
    {
        $events = $item['event'] ?? [];

        if (! is_array($events)) {
            return '';
        }

        $lines = [];

        foreach ($events as $event) {
            if (
                ! is_array($event)
                || ($event['listen'] ?? null) !== 'test'
            ) {
                continue;
            }

            $script = data_get(
                $event,
                'script.exec',
                [],
            );

            if (! is_array($script)) {
                continue;
            }

            foreach ($script as $line) {
                if (is_string($line)) {
                    $lines[] = $line;
                }
            }
        }

        return implode("\n", $lines);
    }

    private function requestByName(
        array $collection,
        string $name,
    ): array {
        foreach ($this->requests($collection) as $request) {
            if ($request['name'] === $name) {
                return $request;
            }
        }

        throw new RuntimeException(
            "Postman request {$name} is missing.",
        );
    }
}
