<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmWorkOrderItem extends Model
{
    public const STATUSES = ['pending', 'done', 'skipped', 'fail'];

    protected $fillable = [
        'pm_work_order_id',
        'pm_task_item_id',
        'step_no',
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
        'status',
        'actual_value',
        'note',
        'photo_path',
        'completed_at',
        'completed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'requires_photo' => 'boolean',
            'requires_signature' => 'boolean',
            'loto_required' => 'boolean',
            'is_critical' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(PmWorkOrder::class, 'pm_work_order_id');
    }

    public function taskItem(): BelongsTo
    {
        return $this->belongsTo(PmTaskItem::class, 'pm_task_item_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
