<?php

namespace App\Support;

use App\Models\ApprovalWorkflow;
use App\Models\DepartmentWorkflowBinding;
use App\Models\DocumentType;
use Illuminate\Support\Collection;

/**
 * Document types shown for department ↔ workflow bindings.
 * Union of active workflows (with document_type) and existing bindings so
 * orphaned types still appear until an admin clears them.
 */
final class WorkflowDocumentTypes
{
    public static function forBindings(): Collection
    {
        $fromMaster = DocumentType::allActive()->pluck('code');

        $fromWorkflows = ApprovalWorkflow::query()
            ->where('is_active', true)
            ->whereNotNull('document_type')
            ->where('document_type', '!=', '')
            ->distinct()
            ->orderBy('document_type')
            ->pluck('document_type');

        $fromBindings = DepartmentWorkflowBinding::query()
            ->distinct()
            ->orderBy('document_type')
            ->pluck('document_type');

        return $fromMaster
            ->merge($fromWorkflows)
            ->merge($fromBindings)
            ->unique()
            ->filter()
            ->sort()
            ->values();
    }
}
