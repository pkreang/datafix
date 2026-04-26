<?php

namespace App\Observers;

use App\Models\SystemChangeLog;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    public function created(Role $role): void
    {
        SystemChangeLog::record(
            entityType: 'role',
            entityId: (string) $role->id,
            action: 'created',
            changedFields: ['name' => ['from' => null, 'to' => $role->name]],
        );
    }

    public function updated(Role $role): void
    {
        $changes = [];
        foreach ($role->getChanges() as $key => $newValue) {
            if (in_array($key, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            $changes[$key] = [
                'from' => $role->getOriginal($key),
                'to' => $newValue,
            ];
        }
        if (! $changes) {
            return;
        }
        SystemChangeLog::record(
            entityType: 'role',
            entityId: (string) $role->id,
            action: 'updated',
            changedFields: $changes,
        );
    }

    public function deleted(Role $role): void
    {
        SystemChangeLog::record(
            entityType: 'role',
            entityId: (string) $role->id,
            action: 'deleted',
            changedFields: ['name' => ['from' => $role->name, 'to' => null]],
        );
    }
}
