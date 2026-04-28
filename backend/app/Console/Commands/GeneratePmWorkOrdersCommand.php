<?php

namespace App\Console\Commands;

use App\Services\Cmms\PmWorkOrderGenerator;
use Illuminate\Console\Command;

class GeneratePmWorkOrdersCommand extends Command
{
    protected $signature = 'pm:generate-work-orders
        {--dry-run : Report what would be generated but do not persist}';

    protected $description = 'Scan active PM plans, generate work orders for those that are due (date or runtime), and flag overdue WOs.';

    public function handle(PmWorkOrderGenerator $generator): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('Dry-run mode — no WOs will be persisted.');
        }

        $flagged = $dryRun ? 0 : $generator->flagOverdue();
        if ($flagged > 0) {
            $this->line("Flagged {$flagged} existing 'due' WO(s) as overdue.");
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        $generated = $generator->generateDueNow();
        $this->info("Generated {$generated} new PM work order(s).");

        return self::SUCCESS;
    }
}
