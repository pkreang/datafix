<?php

namespace App\Listeners;

use App\Events\SparePartStockLow;
use App\Models\User;
use App\Notifications\StockLowNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendStockLowNotification implements ShouldQueue
{
    public function handle(SparePartStockLow $event): void
    {
        $part = $event->sparePart;

        // Notify users with spare_parts.manage permission (direct or via role) in the same branch
        $users = User::permission('spare_parts.manage')
            ->where('branch_id', $part->branch_id)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new StockLowNotification($part));
    }
}
