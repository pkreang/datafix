<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class LookupListItem extends Model
{
    protected $fillable = [
        'list_id',
        'value',
        'label_en',
        'label_th',
        'parent_id',
        'sort_order',
        'is_active',
        'extra',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'extra' => 'array',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(LookupList::class, 'list_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        $clear = fn () => Cache::forget('lookup_registry_sources');
        static::saved($clear);
        static::deleted($clear);
    }
}
