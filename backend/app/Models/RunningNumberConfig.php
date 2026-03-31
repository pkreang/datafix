<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunningNumberConfig extends Model
{
    protected $fillable = [
        'document_type',
        'prefix',
        'digit_count',
        'reset_mode',
        'include_year',
        'include_month',
        'last_number',
        'last_reset_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'digit_count' => 'integer',
            'include_year' => 'boolean',
            'include_month' => 'boolean',
            'last_number' => 'integer',
            'last_reset_at' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
