<?php

namespace App\Services;

use App\Models\ApprovalInstanceStep;
use App\Models\User;
use Illuminate\Support\Collection;

class ApproverResolverService
{
    /**
     * Resolve an approval step to a collection of User models who can act on it.
     */
    public function resolve(ApprovalInstanceStep $step): Collection
    {
        return match ($step->approver_type) {
            'user' => User::where('id', (int) $step->approver_ref)->get(),
            'position' => User::where('position_id', (int) $step->approver_ref)->get(),
            'role' => User::role($step->approver_ref)->get(),
            default => collect(),
        };
    }

    /**
     * Resolve approvers excluding those who already approved (for multi-approval steps).
     */
    public function resolveRemaining(ApprovalInstanceStep $step): Collection
    {
        $approvedUserIds = collect($step->approved_by ?? [])
            ->pluck('user_id')
            ->all();

        return $this->resolve($step)->reject(
            fn (User $user) => in_array($user->id, $approvedUserIds)
        );
    }
}
