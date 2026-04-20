<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BreadcrumbComponentTest extends TestCase
{
    public function test_renders_trail_with_links_for_intermediate_and_span_for_last(): void
    {
        $html = $this->render([
            ['label' => 'Settings', 'url' => 'https://app.test/settings'],
            ['label' => 'Users'],
        ]);

        $this->assertStringContainsString('<nav aria-label="Breadcrumb">', $html);
        $this->assertStringContainsString('href="https://app.test/settings"', $html);
        $this->assertStringContainsString('>Settings</a>', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('>Users</span>', $html);
        $this->assertStringNotContainsString('href="https://app.test/users"', $html);
    }

    public function test_auto_prepends_home_when_missing(): void
    {
        $html = $this->render([['label' => 'Custom Page']]);

        $homeUrl = route('dashboard');
        $this->assertStringContainsString('href="'.$homeUrl.'"', $html);

        $occurrences = substr_count($html, 'href="'.$homeUrl.'"');
        $this->assertSame(1, $occurrences, 'Home should appear exactly once when caller omits it');
    }

    public function test_does_not_double_prepend_when_caller_already_includes_home(): void
    {
        $homeUrl = route('dashboard');

        $html = $this->render([
            ['label' => 'Dashboard', 'url' => $homeUrl],
            ['label' => 'Sub Page'],
        ]);

        $this->assertSame(1, substr_count($html, 'href="'.$homeUrl.'"'));
    }

    public function test_empty_items_still_renders_home_as_final_crumb(): void
    {
        $html = $this->render([]);

        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertMatchesRegularExpression('/<span[^>]*aria-current="page"[^>]*>[^<]*Dashboard[^<]*<\/span>/', $html);
    }

    private function render(array $items): string
    {
        return Blade::render('<x-breadcrumb :items="$items" />', ['items' => $items]);
    }
}
