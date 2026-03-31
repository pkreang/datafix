<?php

namespace App\Listeners\Approval;

use App\Events\Approval\WorkflowCompleted;
use App\Notifications\WorkflowApprovedNotification;
use App\Notifications\WorkflowRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWorkflowOutcomeNotification implements ShouldQueue
{
    public function handle(WorkflowCompleted $event): void
    {
        $instance = $event->instance;
        $requester = $instance->requester;

        if (! $requester) {
            return;
        }

        if ($event->outcome === 'approved') {
            $requester->notify(new WorkflowApprovedNotification($instance));
        } else {
            $requester->notify(new WorkflowRejectedNotification($instance, $event->comment));
        }
    }
}
