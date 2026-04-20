<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

final class PermissionDisplay
{
    public static function label(string $name): string
    {
        $names = Lang::get('permissions_display.names', [], app()->getLocale());
        if (is_array($names) && array_key_exists($name, $names)) {
            return (string) $names[$name];
        }

        return $name;
    }

    public static function module(string $module): string
    {
        $modules = Lang::get('permissions_display.modules', [], app()->getLocale());
        if (is_array($modules) && array_key_exists($module, $modules)) {
            return (string) $modules[$module];
        }

        return Str::title(str_replace('_', ' ', $module));
    }

    public static function action(string $action): string
    {
        $actions = Lang::get('permissions_display.actions', [], app()->getLocale());
        if (is_array($actions) && array_key_exists($action, $actions)) {
            return (string) $actions[$action];
        }

        return Str::title(str_replace('_', ' ', $action));
    }
}
