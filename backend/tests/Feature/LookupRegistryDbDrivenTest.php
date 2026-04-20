<?php

namespace Tests\Feature;

use App\Models\LookupList;
use App\Models\LookupListItem;
use App\Support\LookupRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LookupRegistryDbDrivenTest extends TestCase
{
    use RefreshDatabase;

    public function test_sources_merges_built_in_and_db_lists(): void
    {
        Cache::forget('lookup_registry_sources');
        LookupList::create([
            'key' => 'tax_type',
            'label_en' => 'Tax Type',
            'label_th' => 'ประเภทภาษี',
            'is_active' => true,
        ]);

        $sources = LookupRegistry::sources();
        $this->assertArrayHasKey('user', $sources, 'built-in preserved');
        $this->assertArrayHasKey('tax_type', $sources);
        $this->assertSame('db', $sources['tax_type']['source_type']);
    }

    public function test_get_items_returns_db_items_with_locale_label(): void
    {
        Cache::forget('lookup_registry_sources');
        $list = LookupList::create([
            'key' => 'priority',
            'label_en' => 'Priority',
            'label_th' => 'ระดับ',
            'is_active' => true,
        ]);
        LookupListItem::create(['list_id' => $list->id, 'value' => 'low', 'label_en' => 'Low', 'label_th' => 'ต่ำ', 'is_active' => true, 'sort_order' => 0]);
        LookupListItem::create(['list_id' => $list->id, 'value' => 'high', 'label_en' => 'High', 'label_th' => 'สูง', 'is_active' => true, 'sort_order' => 1]);
        LookupListItem::create(['list_id' => $list->id, 'value' => 'hidden', 'label_en' => 'Hidden', 'label_th' => 'ซ่อน', 'is_active' => false, 'sort_order' => 2]);

        app()->setLocale('th');
        $items = LookupRegistry::getItems('priority');

        $this->assertCount(2, $items, 'inactive item is filtered out');
        $this->assertSame(['value' => 'low', 'display' => 'ต่ำ'], $items[0]);
        $this->assertSame(['value' => 'high', 'display' => 'สูง'], $items[1]);
    }

    public function test_get_items_falls_back_to_english_when_thai_missing(): void
    {
        Cache::forget('lookup_registry_sources');
        $list = LookupList::create([
            'key' => 'unit',
            'label_en' => 'Unit',
            'label_th' => 'หน่วย',
            'is_active' => true,
        ]);
        LookupListItem::create(['list_id' => $list->id, 'value' => 'pc', 'label_en' => 'Piece', 'label_th' => '', 'is_active' => true]);

        app()->setLocale('th');
        $items = LookupRegistry::getItems('unit');
        $this->assertSame('Piece', $items[0]['display']);
    }

    public function test_cache_invalidates_on_list_save(): void
    {
        Cache::forget('lookup_registry_sources');
        $this->assertArrayNotHasKey('unit_x', LookupRegistry::sources());

        LookupList::create([
            'key' => 'unit_x',
            'label_en' => 'U',
            'label_th' => 'U',
            'is_active' => true,
        ]);

        $this->assertArrayHasKey('unit_x', LookupRegistry::sources(), 'cache should be invalidated after save');
    }

    public function test_db_list_cannot_shadow_built_in_key(): void
    {
        Cache::forget('lookup_registry_sources');
        // Even if someone forces a collision into DB, the registry must keep built-in data source.
        LookupList::create([
            'key' => 'user',
            'label_en' => 'Custom User',
            'label_th' => 'Custom User',
            'is_active' => true,
        ]);

        $sources = LookupRegistry::sources();
        $this->assertNotSame('db', $sources['user']['source_type'] ?? 'model');
    }
}
