<?php

namespace App\Console\Commands;

use App\Services\SchemaFirstFormService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PocAnnotateCommand extends Command
{
    protected $signature = 'poc:annotate {table : Name of the table to introspect and annotate}';

    protected $description = 'PoC — introspect a DB table and seed rows into document_form_column_annotations';

    public function handle(SchemaFirstFormService $service): int
    {
        $table = (string) $this->argument('table');

        if (! Schema::hasTable($table)) {
            $this->error("Table `{$table}` does not exist.");

            return self::FAILURE;
        }

        $created = $service->bootstrap($table);
        $this->info("Bootstrapped annotations for `{$table}` — {$created} new rows created (existing rows kept).");

        return self::SUCCESS;
    }
}
