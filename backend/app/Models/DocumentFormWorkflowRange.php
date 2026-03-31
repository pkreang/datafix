<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFormWorkflowRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'min_amount',
        'max_amount',
        'workflow_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
        ];
    }

    public function policy()
    {
        return $this->belongsTo(DocumentFormWorkflowPolicy::class, 'policy_id');
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }
}
