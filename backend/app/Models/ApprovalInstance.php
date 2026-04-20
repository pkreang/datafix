<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'department_id',
        'requester_user_id',
        'document_type',
        'reference_no',
        'payload',
        'current_step_no',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'current_step_no' => 'integer',
        ];
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function steps()
    {
        return $this->hasMany(ApprovalInstanceStep::class)->orderBy('step_no');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * When this instance was started from /forms (generic eForm submission).
     */
    public function formSubmission()
    {
        return $this->hasOne(DocumentFormSubmission::class, 'approval_instance_id');
    }
}
