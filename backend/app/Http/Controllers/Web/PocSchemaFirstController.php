<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DocumentFormColumnAnnotation;
use App\Services\SchemaFirstFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PocSchemaFirstController extends Controller
{
    public function __construct(private readonly SchemaFirstFormService $service) {}

    public function show(string $table): View
    {
        abort_unless(Schema::hasTable($table), 404, "Table `{$table}` not found");

        $fields = $this->service->getFormDefinition($table);

        return view('poc.schema-first.show', [
            'table' => $table,
            'fields' => $fields,
        ]);
    }

    public function submit(Request $request, string $table): RedirectResponse
    {
        abort_unless(Schema::hasTable($table), 404);

        $rules = $this->service->validationRules($table);
        $validated = $request->validate($rules);

        $row = array_merge($validated, [
            'user_id' => session('user.id') ?? 1,
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // JSON-cast for multi_select / array fields
        foreach ($row as $k => $v) {
            if (is_array($v)) {
                $row[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }

        $id = DB::table($table)->insertGetId($row);

        return redirect()->route('poc.schema-first.show', $table)
            ->with('success', "PoC submitted — row #{$id} inserted into `{$table}`.");
    }

    public function annotate(string $table): View
    {
        abort_unless(Schema::hasTable($table), 404, "Table `{$table}` not found");

        // Bootstrap if empty
        if (DocumentFormColumnAnnotation::where('table_name', $table)->doesntExist()) {
            $this->service->bootstrap($table);
        }

        $annotations = DocumentFormColumnAnnotation::where('table_name', $table)
            ->orderBy('sort_order')
            ->get();

        $introspection = collect($this->service->introspect($table))->keyBy('name');

        return view('poc.schema-first.annotate', [
            'table' => $table,
            'annotations' => $annotations,
            'introspection' => $introspection,
        ]);
    }

    public function saveAnnotations(Request $request, string $table): RedirectResponse
    {
        abort_unless(Schema::hasTable($table), 404);

        $payload = (array) $request->input('annotations', []);

        foreach ($payload as $row) {
            if (! isset($row['column_name'])) {
                continue;
            }
            DocumentFormColumnAnnotation::updateOrCreate(
                ['table_name' => $table, 'column_name' => $row['column_name']],
                [
                    'label_en' => $row['label_en'] ?? null,
                    'label_th' => $row['label_th'] ?? null,
                    'ui_type' => $row['ui_type'] ?? 'text',
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_visible' => (bool) ($row['is_visible'] ?? false),
                    'is_required' => (bool) ($row['is_required'] ?? false),
                    'placeholder' => $row['placeholder'] ?? null,
                ]
            );
        }

        return redirect()->route('poc.schema-first.annotate', $table)
            ->with('success', 'Annotations saved.');
    }

    public function bootstrap(string $table): RedirectResponse
    {
        abort_unless(Schema::hasTable($table), 404);

        $created = $this->service->bootstrap($table);

        return redirect()->route('poc.schema-first.annotate', $table)
            ->with('success', "Bootstrap complete — {$created} new annotation rows created.");
    }
}
