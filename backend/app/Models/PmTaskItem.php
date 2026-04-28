<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmTaskItem extends Model
{
    public const TASK_TYPES = ['visual', 'measurement', 'lubrication', 'replacement', 'cleaning', 'tightening', 'other'];

    protected $fillable = [
        'pm_plan_id',
        'step_no',
        'sort_order',
        'description',
        'task_type',
        'expected_value',
        'unit',
        'requires_photo',
        'requires_signature',
        'spare_part_id',
        'estimated_minutes',
        'loto_required',
        'is_critical',
    ];

    protected function casts(): array
    {
        return [
            'requires_photo' => 'boolean',
            'requires_signature' => 'boolean',
            'loto_required' => 'boolean',
            'is_critical' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PmPlan::class, 'pm_plan_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
