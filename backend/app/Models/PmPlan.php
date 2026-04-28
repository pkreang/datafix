<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PmPlan extends Model
{
    public const FREQUENCY_TYPES = ['date', 'runtime'];

    protected $fillable = [
        'equipment_id',
        'name',
        'description',
        'frequency_type',
        'interval_days',
        'interval_hours',
        'last_executed_at',
        'last_executed_runtime',
        'next_due_at',
        'next_due_runtime',
        'assigned_to_position_id',
        'estimated_duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'interval_hours' => 'decimal:2',
            'last_executed_at' => 'datetime',
            'last_executed_runtime' => 'decimal:2',
            'next_due_at' => 'date',
            'next_due_runtime' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function taskItems(): HasMany
    {
        return $this->hasMany(PmTaskItem::class)->orderBy('sort_order')->orderBy('step_no');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(PmWorkOrder::class);
    }

    public function assignedPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'assigned_to_position_id');
    }
}
