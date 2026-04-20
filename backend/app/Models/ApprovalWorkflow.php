<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_type',
        'description',
        'is_active',
        'allow_requester_as_approver',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allow_requester_as_approver' => 'boolean',
        ];
    }

    public function stages()
    {
        return $this->hasMany(ApprovalWorkflowStage::class, 'workflow_id')->orderBy('step_no');
    }
}
