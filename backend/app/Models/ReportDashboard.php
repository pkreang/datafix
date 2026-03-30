<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'layout_columns',
        'visibility',
        'required_permission',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'layout_columns' => 'integer',
        ];
    }

    public function widgets()
    {
        return $this->hasMany(ReportDashboardWidget::class, 'dashboard_id')->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
