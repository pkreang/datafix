<?php

namespace App\Services;

use App\Models\NavigationMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NavigationService
{
    /**
     * Build the menu tree filtered by the current user's permissions.
     */
    public function getMenus(array $permissions, bool $isSuperAdmin): Collection
    {
        $tree = Cache::remember('navigation_menus_tree', 3600, function () {
            return NavigationMenu::rootMenus()->with('children')->get();
        });

        return $tree
            ->map(fn ($menu) => clone $menu)
            ->filter(fn ($menu) => $this->isAccessible($menu, $permissions, $isSuperAdmin))
            ->map(function ($menu) use ($permissions, $isSuperAdmin) {
                if ($menu->children->isNotEmpty()) {
                    $filtered = $menu->children
                        ->map(fn ($c) => clone $c)
                        ->filter(fn ($child) => $this->isAccessible($child, $permissions, $isSuperAdmin));
                    $menu->setRelation('children', $filtered);
                }
                return $menu;
            })
            ->filter(function ($menu) {
                if ($menu->route === null && $menu->children->isEmpty()) {
                    return false;
                }
                return true;
            })
            ->values();
    }

    private function isAccessible(NavigationMenu $menu, array $permissions, bool $isSuperAdmin): bool
    {
        if ($menu->permission === null) {
            return true;
        }
        if ($isSuperAdmin) {
            return true;
        }
        return in_array($menu->permission, $permissions);
    }
}
