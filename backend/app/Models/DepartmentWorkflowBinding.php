<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentWorkflowBinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'document_type',
        'workflow_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }
}
