<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFormField extends Model
{
    use HasFactory;

    /**
     * Field types that can sensibly be filtered on from the list page.
     * Used by the admin form builder to show/hide the "searchable" toggle
     * and by the controller to reject attempts to flag non-searchable types.
     */
    public const SEARCHABLE_TYPES = [
        'text', 'textarea', 'select', 'multi_select', 'date', 'datetime',
        'number', 'email', 'phone', 'radio', 'lookup',
    ];

    protected $fillable = [
        'form_id',
        'field_key',
        'label',
        'field_type',
        'is_required',
        'is_searchable',
        'sort_order',
        'col_span',
        'placeholder',
        'options',
        'visible_to_departments',
        'editable_by',
        'visibility_rules',
        'validation_rules',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'options' => 'array',
            'visible_to_departments' => 'array',
            'editable_by' => 'array',
            'visibility_rules' => 'array',
            'validation_rules' => 'array',
        ];
    }

    public function supportsSearch(): bool
    {
        return in_array($this->field_type, self::SEARCHABLE_TYPES, true);
    }

    /**
     * null → ['requester'] (default: requester only)
     * []   → [] (explicitly nobody — read-only to all)
     */
    public function getEffectiveEditableByAttribute(): array
    {
        return $this->editable_by ?? ['requester'];
    }

    public function form()
    {
        return $this->belongsTo(DocumentForm::class, 'form_id');
    }
}
