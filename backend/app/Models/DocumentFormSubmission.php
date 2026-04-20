<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'user_id',
        'department_id',
        'payload',
        'status',
        'approval_instance_id',
        'reference_no',
        'fdata_row_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function form()
    {
        return $this->belongsTo(DocumentForm::class, 'form_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function instance()
    {
        return $this->belongsTo(ApprovalInstance::class, 'approval_instance_id');
    }

    /**
     * 'draft' | 'pending' | 'approved' | 'rejected' | 'submitted'
     * draft comes from the submission itself; post-submit statuses come from the
     * approval_instance so the UI tracks workflow outcome, not just submission state.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'draft') {
            return 'draft';
        }

        return $this->instance?->status ?? 'submitted';
    }

    /**
     * First scalar value from payload (searchable fields first, then by sort_order)
     * used as a row-level "subject line" so users can identify submissions without
     * opening each one.
     */
    public function getPreviewAttribute(): ?string
    {
        $fields = $this->form?->fields;
        if (! $fields || $fields->isEmpty()) {
            return null;
        }

        $ordered = $fields
            ->sortBy('sort_order')
            ->sortByDesc(fn ($f) => (int) ($f->is_searchable ?? 0))
            ->values();

        foreach ($ordered as $field) {
            $val = $this->payload[$field->field_key] ?? null;
            if (is_scalar($val) && $val !== '' && $val !== null) {
                return (string) $val;
            }
        }

        return null;
    }

    /**
     * Compute the row's action plan (primary / secondary / menu) for a given viewer.
     *
     * @param  array{id:int,can_approve:bool,is_super_admin:bool}  $viewer
     * @return array{primary:?array,secondary:array,menu:array}
     */
    public function actionPlan(array $viewer): array
    {
        $isOwner = (int) $this->user_id === (int) $viewer['id'];
        $canView = $isOwner || $viewer['can_approve'] || $viewer['is_super_admin'];
        $canEditDraft = $isOwner && $this->status === 'draft';
        $canDeleteDraft = $canEditDraft;
        $canDuplicate = $isOwner;
        $canPrint = $canView && $this->status !== 'draft';

        $status = $this->effective_status;

        $viewUrl = route('forms.submission.show', $this);
        $editUrl = route('forms.draft.edit', $this);
        $printUrl = route('forms.submission.print', $this);
        $duplicateUrl = route('forms.submission.duplicate', $this);
        $deleteUrl = route('forms.draft.destroy', $this);

        $primary = null;
        $secondary = [];
        $menu = [];

        // Primary (status-adaptive)
        if ($status === 'draft' && $canEditDraft) {
            $primary = ['label' => __('common.edit'), 'href' => $editUrl];
        } elseif ($status === 'approved' && $canPrint) {
            $primary = ['label' => __('common.action_print'), 'href' => $printUrl, 'target' => '_blank'];
        } elseif ($canView) {
            $primary = ['label' => __('common.view'), 'href' => $viewUrl];
        }

        // Secondary (desktop, intent-reinforcing)
        if ($status === 'pending' && $canPrint) {
            $secondary[] = ['label' => __('common.action_print'), 'href' => $printUrl, 'target' => '_blank'];
        }
        if ($status === 'approved' && $canView) {
            $secondary[] = ['label' => __('common.view'), 'href' => $viewUrl];
        }
        if ($status === 'rejected' && $canDuplicate) {
            $secondary[] = ['label' => __('common.action_duplicate'), 'action' => $duplicateUrl, 'method' => 'POST'];
        }

        // Menu
        if ($canDuplicate) {
            $menu[] = [
                'label' => __('common.action_duplicate'),
                'action' => $duplicateUrl,
                'method' => 'POST',
                'icon' => 'duplicate',
            ];
        }
        if ($canPrint && $status !== 'approved' && $status !== 'pending') {
            // rejected status — print lives in menu
            $menu[] = [
                'label' => __('common.action_print'),
                'href' => $printUrl,
                'icon' => 'print',
            ];
        }
        if ($canDeleteDraft) {
            $menu[] = [
                'label' => __('common.action_delete_draft'),
                'action' => $deleteUrl,
                'method' => 'DELETE',
                'icon' => 'delete',
                'class' => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30',
                'confirm' => __('common.confirm_delete'),
            ];
        }

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'menu' => $menu,
        ];
    }
}
