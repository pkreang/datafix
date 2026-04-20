<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * School vertical copy lives under resources/lang/verticals/school/{locale}/*.php
 * (same layout as the main lang path). We append that path to the FileLoader so
 * Laravel merges overrides last — Lang::addLines is overwritten when a group loads.
 */
final class OrganizationTranslations
{
    public static function registerLoaderPath(): void
    {
        if ((string) config('organization.vertical', 'factory') !== 'school') {
            return;
        }

        $path = lang_path('verticals/school');
        if (! File::isDirectory($path)) {
            return;
        }

        app('translation.loader')->addPath($path);
    }
}
