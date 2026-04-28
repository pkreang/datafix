<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkOrder extends Model
{
    use SoftDeletes;

    public const STATUSES = ['due', 'in_progress', 'done', 'skipped', 'overdue', 'cancelled'];

    protected $fillable = [
        'pm_plan_id',
        'equipment_id',
        'code',
        'status',
        'due_date',
        'generated_at',
        'assigned_to_user_id',
        'started_at',
        'completed_at',
        'completed_by_user_id',
        'findings',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'generated_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PmPlan::class, 'pm_plan_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PmWorkOrderItem::class)->orderBy('step_no');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
