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

    public function test_two_item_trail_does_not_auto_prepend_home(): void
    {
        // Threshold raised to 3 — 2-item trail must NOT pull Dashboard in front.
        // Section-level pages (e.g. /settings/branding, /forms/{key}/submissions)
        // render their own intermediate as the visible root.
        $homeUrl = route('dashboard');
        $html = $this->render([
            ['label' => 'Settings', 'url' => 'https://app.test/settings'],
            ['label' => 'Detail'],
        ]);

        $this->assertSame(0, substr_count($html, 'href="'.$homeUrl.'"'),
            'Home link should be absent on 2-item trail under the new threshold');
        $this->assertStringContainsString('>Settings</a>', $html);
        $this->assertStringContainsString('>Detail</span>', $html);
    }

    public function test_three_or_more_item_trail_auto_prepends_home(): void
    {
        $homeUrl = route('dashboard');
        $html = $this->render([
            ['label' => 'Settings', 'url' => 'https://app.test/settings'],
            ['label' => 'Lookups', 'url' => 'https://app.test/settings/lookups'],
            ['label' => 'Edit'],
        ]);

        $this->assertSame(1, substr_count($html, 'href="'.$homeUrl.'"'),
            'Home should appear exactly once for 3-item trails');
    }

    public function test_single_item_trail_does_not_auto_prepend_home(): void
    {
        // 1-item (top-level) trail must NOT pull Dashboard in front — that
        // duplicates the page <h1> and the sidebar Home link.
        $html = $this->render([['label' => 'Custom Page']]);

        $homeUrl = route('dashboard');
        $this->assertSame(0, substr_count($html, 'href="'.$homeUrl.'"'),
            'Home link should be absent on single-item trail');
        $this->assertStringContainsString('>Custom Page</span>', $html);
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

    public function test_empty_items_renders_no_crumbs(): void
    {
        // Caller passed no items → trail stays empty (0 items < 2 threshold).
        // This is the historical contract: an empty <ol> is fine; nothing
        // crashes, no Dashboard label is forced into existence.
        $html = $this->render([]);

        $this->assertStringContainsString('<nav aria-label="Breadcrumb">', $html);
        $this->assertStringNotContainsString('aria-current="page"', $html);
    }

    private function render(array $items): string
    {
        return Blade::render('<x-breadcrumb :items="$items" />', ['items' => $items]);
    }
}
