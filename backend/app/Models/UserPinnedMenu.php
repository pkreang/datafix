<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPinnedMenu extends Model
{
    protected $fillable = ['user_id', 'menu_key', 'sort_order'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function keysFor(int $userId): array
    {
        return static::where('user_id', $userId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('menu_key')
            ->toArray();
    }

    public static function toggle(int $userId, string $menuKey): bool
    {
        $existing = static::where('user_id', $userId)->where('menu_key', $menuKey)->first();
        if ($existing) {
            $existing->delete();

            return false;
        }
        $next = (int) static::where('user_id', $userId)->max('sort_order') + 1;
        static::create(['user_id' => $userId, 'menu_key' => $menuKey, 'sort_order' => $next]);

        return true;
    }
}
