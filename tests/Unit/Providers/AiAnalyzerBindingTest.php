<?php

namespace Tests\Unit\Providers;

use App\Contracts\Ai\AiAnalyzer;
use App\Infrastructure\Ai\GroqAiAnalyzer;
use Tests\TestCase;

class AiAnalyzerBindingTest extends TestCase
{
    public function test_ai_analyzer_contract_resolves_to_groq_adapter(): void
    {
        $firstResolution = $this->app->make(
            AiAnalyzer::class,
        );

        $secondResolution = $this->app->make(
            AiAnalyzer::class,
        );

        $this->assertInstanceOf(
            GroqAiAnalyzer::class,
            $firstResolution,
        );

        $this->assertSame(
            $firstResolution,
            $secondResolution,
        );
    }
}
