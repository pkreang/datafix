<?php

namespace App\Listeners\Approval;

use App\Events\Approval\WorkflowStarted;
use App\Events\Approval\WorkflowStepAdvanced;
use App\Models\ApprovalInstanceStep;
use App\Notifications\ApprovalPendingNotification;
use App\Services\ApproverResolverService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendApprovalPendingNotification implements ShouldQueue
{
    public function __construct(
        private readonly ApproverResolverService $resolver,
    ) {}

    public function handle(WorkflowStarted|WorkflowStepAdvanced $event): void
    {
        $instance = $event->instance;

        $step = $event instanceof WorkflowStepAdvanced
            ? $event->nextStep
            : $instance->steps->firstWhere('step_no', $instance->current_step_no);

        if (! $step instanceof ApprovalInstanceStep) {
            return;
        }

        $approvers = $this->resolver->resolve($step);

        if ($approvers->isNotEmpty()) {
            Notification::send($approvers, new ApprovalPendingNotification($instance, $step));
        }
    }
}
