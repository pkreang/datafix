<?php

namespace App\Listeners\Approval;

use App\Events\Approval\WorkflowPartialApproval;
use App\Notifications\ApprovalPendingNotification;
use App\Services\ApproverResolverService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendPartialApprovalNotification implements ShouldQueue
{
    public function __construct(
        private readonly ApproverResolverService $resolver,
    ) {}

    public function handle(WorkflowPartialApproval $event): void
    {
        $remaining = $this->resolver->resolveRemaining($event->step);

        if ($remaining->isNotEmpty()) {
            Notification::send($remaining, new ApprovalPendingNotification($event->instance, $event->step));
        }
    }
}
