<?php

namespace App\Observers;

use App\Models\DocumentType;
use App\Models\SystemChangeLog;

class DocumentTypeObserver
{
    private const SKIP_KEYS = ['id', 'created_at', 'updated_at'];

    public function created(DocumentType $type): void
    {
        SystemChangeLog::record(
            entityType: 'document_type',
            entityId: $type->code,
            action: 'created',
            changedFields: ['code' => ['from' => null, 'to' => $type->code]],
        );
    }

    public function updated(DocumentType $type): void
    {
        $changes = [];
        foreach ($type->getChanges() as $key => $newValue) {
            if (in_array($key, self::SKIP_KEYS, true)) {
                continue;
            }
            $changes[$key] = [
                'from' => $type->getOriginal($key),
                'to' => $newValue,
            ];
        }
        if (! $changes) {
            return;
        }
        SystemChangeLog::record(
            entityType: 'document_type',
            entityId: $type->code,
            action: 'updated',
            changedFields: $changes,
        );
    }

    public function deleted(DocumentType $type): void
    {
        SystemChangeLog::record(
            entityType: 'document_type',
            entityId: $type->code,
            action: 'deleted',
            changedFields: ['code' => ['from' => $type->code, 'to' => null]],
        );
    }
}
