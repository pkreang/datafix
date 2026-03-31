<?php

namespace App\Events\Approval;

use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class WorkflowPartialApproval implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly ApprovalInstance $instance,
        public readonly ApprovalInstanceStep $step,
    ) {}
}
