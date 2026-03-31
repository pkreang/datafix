<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalInstanceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_instance_id',
        'step_no',
        'stage_name',
        'approver_type',
        'approver_ref',
        'min_approvals',
        'approved_by',
        'acted_by_user_id',
        'action',
        'comment',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'step_no' => 'integer',
            'min_approvals' => 'integer',
            'approved_by' => 'array',
            'acted_at' => 'datetime',
        ];
    }

    public function approvalInstance()
    {
        return $this->belongsTo(ApprovalInstance::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }
}
