<?php

namespace App\Services;

use App\Models\RunningNumberConfig;
use Illuminate\Support\Facades\DB;

class RunningNumberService
{
    /**
     * Generate the next running number for a document type.
     * Returns null if no active config exists.
     */
    public function generate(string $documentType): ?string
    {
        $config = RunningNumberConfig::where('document_type', $documentType)
            ->where('is_active', true)
            ->first();

        if (! $config) {
            return null;
        }

        return DB::transaction(function () use ($config) {
            $config = RunningNumberConfig::lockForUpdate()->find($config->id);

            $now = now();

            if ($this->shouldReset($config, $now)) {
                $config->last_number = 0;
                $config->last_reset_at = $now->toDateString();
            }

            $config->last_number++;
            $config->save();

            return $this->format($config, $now);
        });
    }

    private function shouldReset(RunningNumberConfig $config, $now): bool
    {
        if ($config->reset_mode === 'none') {
            return false;
        }

        if (! $config->last_reset_at) {
            return true;
        }

        $lastReset = $config->last_reset_at;

        return match ($config->reset_mode) {
            'yearly' => $now->year !== $lastReset->year,
            'monthly' => $now->year !== $lastReset->year || $now->month !== $lastReset->month,
            default => false,
        };
    }

    private function format(RunningNumberConfig $config, $now): string
    {
        $parts = [$config->prefix];

        if ($config->include_year) {
            $parts[] = $now->format('Y');
        }

        if ($config->include_month) {
            $parts[] = $now->format('m');
        }

        $parts[] = '-';
        $parts[] = str_pad($config->last_number, $config->digit_count, '0', STR_PAD_LEFT);

        return implode('', $parts);
    }
}
