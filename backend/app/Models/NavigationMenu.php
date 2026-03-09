<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class NavigationMenu extends Model
{
    protected $fillable = [
        'parent_id', 'label', 'icon', 'route',
        'permission', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function allChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeRootMenus(Builder $query): Builder
    {
        return $query->whereNull('parent_id')
                     ->where('is_active', true)
                     ->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────

    public function isActive(): bool
    {
        if ($this->route === null) {
            return false;
        }

        $path = ltrim($this->route, '/');

        return request()->is($path) || request()->is($path . '/*');
    }

    public function hasActiveChild(): bool
    {
        return $this->children->contains(fn (self $child) => $child->isActive());
    }

    // ── Cache invalidation ────────────────────────────────

    protected static function booted(): void
    {
        $clear = fn () => Cache::forget('navigation_menus_tree');

        static::saved($clear);
        static::deleted($clear);
    }
}
