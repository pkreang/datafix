<?php

namespace App\Events\Approval;

use App\Models\ApprovalInstance;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class WorkflowCompleted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly ApprovalInstance $instance,
        public readonly string $outcome, // 'approved' or 'rejected'
        public readonly ?string $comment = null,
    ) {}
}
