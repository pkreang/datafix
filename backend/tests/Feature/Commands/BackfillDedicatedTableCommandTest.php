<?php

namespace Tests\Feature\Commands;

use App\Models\DocumentForm;
use App\Models\DocumentFormSubmission;
use App\Services\FormSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillDedicatedTableCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfills_submissions_with_null_fdata_row_id(): void
    {
        $form = $this->makeFormWithTable('bf_happy', [
            ['field_key' => 'title', 'field_type' => 'text'],
            ['field_key' => 'tags', 'field_type' => 'multi_select'],
        ]);

        $submissions = collect(range(1, 3))->map(fn ($i) => DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 10 + $i,
            'payload' => ['title' => "Row {$i}", 'tags' => ["tag{$i}"]],
            'status' => 'draft',
        ]));

        Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_happy']);

        $rows = DB::table($form->submission_table)->get();
        $this->assertCount(3, $rows);

        foreach ($submissions as $sub) {
            $sub->refresh();
            $this->assertNotNull($sub->fdata_row_id);
            $row = DB::table($form->submission_table)->find($sub->fdata_row_id);
            $this->assertSame($sub->payload['title'], $row->title);
            $this->assertSame($sub->payload['tags'], json_decode($row->tags, true));
            $this->assertSame($sub->user_id, (int) $row->user_id);
            $this->assertSame('draft', $row->status);
        }
    }

    public function test_is_idempotent_on_second_run(): void
    {
        $form = $this->makeFormWithTable('bf_idem', [
            ['field_key' => 'title', 'field_type' => 'text'],
        ]);

        DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['title' => 'One'],
            'status' => 'draft',
        ]);

        Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_idem']);
        $this->assertSame(1, DB::table($form->submission_table)->count());

        Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_idem']);
        $this->assertSame(1, DB::table($form->submission_table)->count(), 'second run must not re-insert');
        $output = Artisan::output();
        $this->assertStringContainsString('No submissions to backfill', $output);
    }

    public function test_dry_run_does_not_write(): void
    {
        $form = $this->makeFormWithTable('bf_dry', [
            ['field_key' => 'title', 'field_type' => 'text'],
        ]);

        $sub = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['title' => 'Nope'],
            'status' => 'draft',
        ]);

        Artisan::call('forms:backfill-dedicated-table', [
            'form_key' => 'bf_dry',
            '--dry-run' => true,
        ]);

        $this->assertSame(0, DB::table($form->submission_table)->count());
        $sub->refresh();
        $this->assertNull($sub->fdata_row_id);
        $this->assertStringContainsString('[DRY-RUN]', Artisan::output());
    }

    public function test_force_rebuilds_existing_fdata_row(): void
    {
        $form = $this->makeFormWithTable('bf_force', [
            ['field_key' => 'title', 'field_type' => 'text'],
        ]);

        $sub = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['title' => 'Current'],
            'status' => 'draft',
        ]);

        Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_force']);
        $sub->refresh();
        $oldRowId = $sub->fdata_row_id;
        $this->assertNotNull($oldRowId);

        Artisan::call('forms:backfill-dedicated-table', [
            'form_key' => 'bf_force',
            '--force' => true,
        ]);

        $sub->refresh();
        $this->assertNotNull($sub->fdata_row_id);
        $this->assertNotSame($oldRowId, $sub->fdata_row_id, 'force should insert a new row');
        $this->assertSame(1, DB::table($form->submission_table)->count(), 'old row should be deleted');
        $this->assertDatabaseMissing($form->submission_table, ['id' => $oldRowId]);
    }

    public function test_fails_when_form_has_no_dedicated_table(): void
    {
        $form = DocumentForm::create([
            'form_key' => 'bf_notable',
            'name' => 'No Table',
            'document_type' => 'maintenance_request',
            'is_active' => true,
            'layout_columns' => 1,
            'submission_table' => null,
        ]);

        DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['x' => 'y'],
            'status' => 'draft',
        ]);

        $exitCode = Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_notable']);

        $this->assertSame(1, $exitCode, 'should return FAILURE');
        $this->assertStringContainsString('no submission_table', Artisan::output());
    }

    public function test_fails_when_form_key_not_found(): void
    {
        $exitCode = Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'nonexistent']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('not found', Artisan::output());
    }

    public function test_silently_drops_payload_keys_missing_from_schema(): void
    {
        $form = $this->makeFormWithTable('bf_orphan', [
            ['field_key' => 'title', 'field_type' => 'text'],
        ]);

        $sub = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['title' => 'Keep', 'deleted_field' => 'should be dropped'],
            'status' => 'draft',
        ]);

        Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_orphan']);

        $sub->refresh();
        $this->assertNotNull($sub->fdata_row_id);
        $row = DB::table($form->submission_table)->find($sub->fdata_row_id);
        $this->assertSame('Keep', $row->title);
        $this->assertFalse(Schema::hasColumn($form->submission_table, 'deleted_field'));
    }

    public function test_counts_failed_rows_without_aborting_batch(): void
    {
        $form = $this->makeFormWithTable('bf_mismatch', [
            ['field_key' => 'title', 'field_type' => 'text'],
            ['field_key' => 'amount', 'field_type' => 'number'],
        ]);

        DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['title' => 'Good', 'amount' => 123.45],
            'status' => 'draft',
        ]);
        DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => ['title' => 'Bad', 'amount' => 'not-a-number'],
            'status' => 'draft',
        ]);

        $exitCode = Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_mismatch']);

        $output = Artisan::output();

        // One of two possible outcomes depending on DB strict mode:
        // - Strict: "Bad" row fails, good row succeeds, exit FAILURE
        // - Lax (sqlite): both succeed (sqlite accepts "not-a-number" in numeric column)
        // Either way, the batch must not abort early — the "Good" row must be present.
        $this->assertStringContainsString('Good', DB::table($form->submission_table)->pluck('title')->implode(','));

        if (str_contains($output, 'Failed: 1')) {
            $this->assertSame(1, $exitCode);
            $this->assertSame(1, DB::table($form->submission_table)->count());
        } else {
            $this->assertSame(0, $exitCode);
            $this->assertSame(2, DB::table($form->submission_table)->count());
        }
    }

    public function test_skips_submissions_with_empty_payload(): void
    {
        $form = $this->makeFormWithTable('bf_empty', [
            ['field_key' => 'title', 'field_type' => 'text'],
        ]);

        DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => 1,
            'payload' => [],
            'status' => 'draft',
        ]);

        Artisan::call('forms:backfill-dedicated-table', ['form_key' => 'bf_empty']);

        $this->assertSame(0, DB::table($form->submission_table)->count());
        $this->assertStringContainsString('Skipped (empty payload): 1', Artisan::output());
    }

    /**
     * Helper: build a form + fields + dedicated table ready for backfill.
     */
    private function makeFormWithTable(string $key, array $fields): DocumentForm
    {
        $form = DocumentForm::create([
            'form_key' => $key,
            'name' => ucfirst($key),
            'document_type' => 'maintenance_request',
            'is_active' => true,
            'layout_columns' => 1,
            'submission_table' => 'fdata_'.$key,
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

        $form->load('fields');
        app(FormSchemaService::class)->createTable($form);

        return $form;
    }
}
