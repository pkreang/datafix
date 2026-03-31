<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_instance_id',
        'spare_part_id',
        'quantity_requested',
        'quantity_issued',
        'unit_cost',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:2',
            'quantity_issued' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function approvalInstance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
