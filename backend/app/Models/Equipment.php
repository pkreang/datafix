<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'code',
        'serial_number',
        'equipment_category_id',
        'equipment_location_id',
        'company_id',
        'branch_id',
        'status',
        'installed_date',
        'warranty_expiry',
        'specifications',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'specifications' => 'array',
            'installed_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function location()
    {
        return $this->belongsTo(EquipmentLocation::class, 'equipment_location_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
