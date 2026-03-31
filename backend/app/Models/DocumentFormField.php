<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'field_key',
        'label',
        'field_type',
        'is_required',
        'sort_order',
        'col_span',
        'placeholder',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    public function form()
    {
        return $this->belongsTo(DocumentForm::class, 'form_id');
    }
}
