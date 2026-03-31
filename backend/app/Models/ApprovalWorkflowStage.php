<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'step_no',
        'name',
        'approver_type',
        'approver_ref',
        'min_approvals',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'step_no' => 'integer',
            'min_approvals' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }
}
