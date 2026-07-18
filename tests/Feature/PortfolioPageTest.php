<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioPageTest extends TestCase
{
    public function test_portfolio_home_page_is_rendered(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Backend PHP / Laravel Developer')
            ->assertSee('Создаю надёжные')
            ->assertSee('contact-form', false)
            ->assertSee('/assets/portfolio.css', false)
            ->assertSee('/assets/portfolio.js', false)
            ->assertSee('/api/health', false)
            ->assertSee('/api/metrics', false)
            ->assertSee('/api/contact', false);
    }

    public function test_portfolio_assets_exist_and_use_real_api_routes(): void
    {
        $stylePath = public_path(
            'assets/portfolio.css',
        );

        $scriptPath = public_path(
            'assets/portfolio.js',
        );

        $this->assertFileExists($stylePath);
        $this->assertFileExists($scriptPath);

        $script = file_get_contents($scriptPath);

        $this->assertIsString($script);
        $this->assertStringContainsString(
            "fetchJson('/api/health')",
            $script,
        );
        $this->assertStringContainsString(
            "fetchJson('/api/metrics')",
            $script,
        );
        $this->assertStringContainsString(
            "'/api/contact'",
            $script,
        );
        $this->assertStringContainsString(
            "method: 'POST'",
            $script,
        );
    }
}
