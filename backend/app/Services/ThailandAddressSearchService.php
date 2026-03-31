<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ThailandAddressSearchService
{
    private const CACHE_KEY = 'thailand_subdistricts_payload';

    private const CACHE_TTL_SECONDS = 86400;

    /** @return list<array{t: string, a: string, p: string, z: string, i: int}> */
    public function allRows(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            $path = resource_path('data/thailand_subdistricts.min.json');
            if (! is_readable($path)) {
                return [];
            }
            $json = file_get_contents($path);
            if ($json === false) {
                return [];
            }
            $data = json_decode($json, true);

            return is_array($data) ? $data : [];
        });
    }

    /**
     * @return list<array{t: string, a: string, p: string, z: string, i: int}>
     */
    public function searchSubdistricts(string $query, int $limit = 30): array
    {
        $q = trim($query);
        if (mb_strlen($q, 'UTF-8') < 2) {
            return [];
        }

        $rows = $this->allRows();
        $out = [];

        foreach ($rows as $row) {
            if (! isset($row['t'], $row['a'], $row['p'], $row['z'])) {
                continue;
            }
            $name = (string) $row['t'];
            if (mb_stripos($name, $q, 0, 'UTF-8') === false) {
                continue;
            }
            $out[] = [
                't' => $name,
                'a' => (string) $row['a'],
                'p' => (string) $row['p'],
                'z' => (string) $row['z'],
                'i' => (int) ($row['i'] ?? 0),
            ];
            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
