<?php

namespace App\Console\Commands;

use App\Models\DocumentForm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentFormsPurgeCommand extends Command
{
    protected $signature = 'document-forms:purge {--force : Skip confirmation}';

    protected $description = 'Delete all document forms (fields, policies, submissions, etc. cascade from DB).';

    public function handle(): int
    {
        $count = DocumentForm::query()->count();

        if ($count === 0) {
            $this->info('No document forms to delete.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            if (! $this->confirm("Delete all {$count} document form(s) and related rows? This cannot be undone.")) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $dedicatedTables = DocumentForm::query()
            ->whereNotNull('submission_table')
            ->pluck('submission_table')
            ->all();

        DB::transaction(function () use ($count): void {
            DocumentForm::query()->delete();
            $this->info("Deleted {$count} document form(s).");
        });

        foreach ($dedicatedTables as $t) {
            Schema::dropIfExists($t);
            $this->info("  Dropped: {$t}");
        }

        return self::SUCCESS;
    }
}
