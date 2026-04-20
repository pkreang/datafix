<?php

namespace Tests\Unit;

use App\Models\DocumentForm;
use App\Services\FormSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FormSchemaServiceSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_table_adds_reserved_columns_and_maps_field_types(): void
    {
        $form = $this->makeFormWithFields('sync_basic', [
            ['field_key' => 'title', 'field_type' => 'text'],
            ['field_key' => 'description', 'field_type' => 'textarea'],
            ['field_key' => 'amount', 'field_type' => 'currency'],
            ['field_key' => 'quantity', 'field_type' => 'number'],
            ['field_key' => 'scheduled_on', 'field_type' => 'date'],
            ['field_key' => 'tags', 'field_type' => 'multi_select'],
            ['field_key' => 'section_divider', 'field_type' => 'section'],
            ['field_key' => 'doc_no', 'field_type' => 'auto_number'],
        ]);

        app(FormSchemaService::class)->createTable($form);
        $table = 'fdata_sync_basic';

        foreach (FormSchemaService::RESERVED_COLUMNS as $reserved) {
            $this->assertTrue(
                Schema::hasColumn($table, $reserved),
                "Reserved column [$reserved] should exist"
            );
        }

        $this->assertTrue(Schema::hasColumn($table, 'title'));
        $this->assertTrue(Schema::hasColumn($table, 'description'));
        $this->assertTrue(Schema::hasColumn($table, 'amount'));
        $this->assertTrue(Schema::hasColumn($table, 'quantity'));
        $this->assertTrue(Schema::hasColumn($table, 'scheduled_on'));
        $this->assertTrue(Schema::hasColumn($table, 'tags'));

        $this->assertFalse(Schema::hasColumn($table, 'section_divider'), 'section fields never become columns');
        $this->assertFalse(Schema::hasColumn($table, 'doc_no'), 'auto_number fields never become columns');
    }

    public function test_sync_table_drops_removed_columns_but_keeps_reserved(): void
    {
        $form = $this->makeFormWithFields('sync_drop', [
            ['field_key' => 'keeper', 'field_type' => 'text'],
            ['field_key' => 'goner', 'field_type' => 'text'],
        ]);

        app(FormSchemaService::class)->createTable($form);
        $table = 'fdata_sync_drop';

        $this->assertTrue(Schema::hasColumn($table, 'goner'));

        $form->fields()->where('field_key', 'goner')->delete();
        app(FormSchemaService::class)->syncTable($form, $form->fields()->get());

        $this->assertFalse(Schema::hasColumn($table, 'goner'));
        $this->assertTrue(Schema::hasColumn($table, 'keeper'));

        foreach (FormSchemaService::RESERVED_COLUMNS as $reserved) {
            $this->assertTrue(
                Schema::hasColumn($table, $reserved),
                "Reserved column [$reserved] must never be dropped"
            );
        }
    }

    public function test_insert_and_update_row_roundtrip_json_payload(): void
    {
        $form = $this->makeFormWithFields('sync_rows', [
            ['field_key' => 'title', 'field_type' => 'text'],
            ['field_key' => 'tags', 'field_type' => 'multi_select'],
        ]);
        $form->update(['submission_table' => 'fdata_sync_rows']);
        $form->refresh()->load('fields');

        $service = app(FormSchemaService::class);
        $service->createTable($form);
        $table = 'fdata_sync_rows';

        $rowId = $service->insertRow(
            $form,
            ['title' => 'Initial', 'tags' => ['urgent', 'night']],
            ['user_id' => 1, 'status' => 'draft']
        );

        $this->assertNotNull($rowId);

        $row = DB::table($table)->find($rowId);
        $this->assertSame('Initial', $row->title);
        $this->assertSame(['urgent', 'night'], json_decode($row->tags, true));
        $this->assertSame('draft', $row->status);

        $service->updateRow(
            $form,
            $rowId,
            ['title' => 'Updated', 'tags' => ['low']],
            ['status' => 'submitted']
        );

        $updated = DB::table($table)->find($rowId);
        $this->assertSame('Updated', $updated->title);
        $this->assertSame(['low'], json_decode($updated->tags, true));
        $this->assertSame('submitted', $updated->status);
    }

    private function makeFormWithFields(string $key, array $fields): DocumentForm
    {
        $form = DocumentForm::create([
            'form_key' => $key,
            'name' => ucfirst($key),
            'document_type' => 'maintenance_request',
            'is_active' => true,
            'layout_columns' => 1,
            'submission_table' => null,
        ]);

        foreach ($fields as $i => $field) {
            $form->fields()->create([
                'field_key' => $field['field_key'],
                'label' => $field['field_key'],
                'field_type' => $field['field_type'],
                'is_required' => false,
                'sort_order' => $i + 1,
            ]);
        }

        return $form->load('fields');
    }
}
