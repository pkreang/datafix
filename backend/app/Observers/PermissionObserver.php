<?php

namespace App\Observers;

use App\Models\SystemChangeLog;
use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    public function created(Permission $permission): void
    {
        SystemChangeLog::record(
            entityType: 'permission',
            entityId: (string) $permission->id,
            action: 'created',
            changedFields: ['name' => ['from' => null, 'to' => $permission->name]],
        );
    }

    public function updated(Permission $permission): void
    {
        $changes = [];
        foreach ($permission->getChanges() as $key => $newValue) {
            if (in_array($key, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            $changes[$key] = [
                'from' => $permission->getOriginal($key),
                'to' => $newValue,
            ];
        }
        if (! $changes) {
            return;
        }
        SystemChangeLog::record(
            entityType: 'permission',
            entityId: (string) $permission->id,
            action: 'updated',
            changedFields: $changes,
        );
    }

    public function deleted(Permission $permission): void
    {
        SystemChangeLog::record(
            entityType: 'permission',
            entityId: (string) $permission->id,
            action: 'deleted',
            changedFields: ['name' => ['from' => $permission->name, 'to' => null]],
        );
    }
}
