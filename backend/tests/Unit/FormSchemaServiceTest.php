<?php

namespace Tests\Unit;

use App\Models\DocumentForm;
use App\Services\FormSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FormSchemaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_table_respects_custom_submission_table(): void
    {
        $service = app(FormSchemaService::class);

        $form = DocumentForm::create([
            'form_key' => 'foo_test',
            'name' => 'Foo',
            'document_type' => 'maintenance_request',
            'is_active' => true,
            'layout_columns' => 1,
            'submission_table' => 'foo_custom',
        ]);
        $form->fields()->create([
            'field_key' => 'initial', 'label' => 'Initial', 'field_type' => 'text',
            'is_required' => false, 'sort_order' => 1,
        ]);

        $service->createTable($form->load('fields'));
        $this->assertTrue(Schema::hasTable('foo_custom'));
        $this->assertTrue(Schema::hasColumn('foo_custom', 'initial'));

        $form->fields()->create([
            'field_key' => 'added_after', 'label' => 'Added After', 'field_type' => 'date',
            'is_required' => false, 'sort_order' => 2,
        ]);

        $service->syncTable($form, $form->fields()->get());

        $this->assertTrue(
            Schema::hasColumn('foo_custom', 'added_after'),
            'syncTable must add new columns to the custom submission_table, not fdata_<form_key>'
        );
        $this->assertFalse(Schema::hasTable('fdata_foo_test'));
    }
}
