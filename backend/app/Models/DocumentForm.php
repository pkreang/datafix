<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_key',
        'name',
        'document_type',
        'description',
        'is_active',
        'layout_columns',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fields()
    {
        return $this->hasMany(DocumentFormField::class, 'form_id')->orderBy('sort_order');
    }

    public function workflowPolicies()
    {
        return $this->hasMany(DocumentFormWorkflowPolicy::class, 'form_id');
    }
}
