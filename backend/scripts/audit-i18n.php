<?php

declare(strict_types=1);

/**
 * Compare EN vs TH translation keys (flattened) for resources/lang PHP files and JSON.
 */
function flattenArray(array $array, string $prefix = ''): array
{
    $out = [];
    foreach ($array as $key => $value) {
        $k = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        if (is_array($value) && $value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
            $out += flattenArray($value, $k);
        } else {
            $out[$k] = $value;
        }
    }

    return $out;
}

$base = dirname(__DIR__).'/resources/lang';
$onlyEn = [];
$onlyTh = [];
$fileReports = [];

foreach (glob($base.'/en/*.php') ?: [] as $enFile) {
    $name = basename($enFile);
    $thFile = $base.'/th/'.$name;
    if (! is_file($thFile)) {
        $fileReports[] = "Missing TH counterpart for en/{$name}";

        continue;
    }
    $en = flattenArray(include $enFile);
    $th = flattenArray(include $thFile);
    $ke = array_keys($en);
    $kt = array_keys($th);
    sort($ke);
    sort($kt);
    $missingTh = array_diff($ke, $kt);
    $missingEn = array_diff($kt, $ke);
    if ($missingTh !== [] || $missingEn !== []) {
        $fileReports[] = $name.':';
        foreach ($missingTh as $k) {
            $onlyEn[] = "{$name} :: {$k}";
        }
        foreach ($missingEn as $k) {
            $onlyTh[] = "{$name} :: {$k}";
        }
    }
}

echo "=== resources/lang EN vs TH (flattened keys) ===\n";
if ($fileReports === []) {
    echo "OK — all PHP pairs have identical key sets.\n";
} else {
    echo implode("\n", $fileReports)."\n";
}

if ($onlyEn !== []) {
    echo "\n--- Only in EN (".count($onlyEn).") ---\n";
    echo implode("\n", $onlyEn)."\n";
}
if ($onlyTh !== []) {
    echo "\n--- Only in TH (".count($onlyTh).") ---\n";
    echo implode("\n", $onlyTh)."\n";
}

// JSON
$jsonEn = dirname(__DIR__).'/lang/en.json';
$jsonTh = dirname(__DIR__).'/lang/th.json';
if (is_file($jsonEn) && is_file($jsonTh)) {
    $je = json_decode(file_get_contents($jsonEn), true) ?: [];
    $jt = json_decode(file_get_contents($jsonTh), true) ?: [];
    $ke = array_keys($je);
    $kt = array_keys($jt);
    sort($ke);
    sort($kt);
    echo "\n=== lang/en.json vs lang/th.json ===\n";
    $d1 = array_diff($ke, $kt);
    $d2 = array_diff($kt, $ke);
    if ($d1 === [] && $d2 === []) {
        echo 'OK — '.count($ke)." keys match.\n";
    } else {
        if ($d1 !== []) {
            echo 'Only EN: '.implode(', ', $d1)."\n";
        }
        if ($d2 !== []) {
            echo 'Only TH: '.implode(', ', $d2)."\n";
        }
    }
} else {
    echo "\n(JSON skipped: en.json/th.json not both present under lang/)\n";
}

// Drift: resources/lang vs lang/ for same PHP file (key count)
echo "\n=== resources/lang vs lang/ PHP (key count drift) ===\n";
foreach (glob($base.'/en/*.php') ?: [] as $enFile) {
    $name = basename($enFile);
    $legacy = dirname(__DIR__).'/lang/en/'.$name;
    if (! is_file($legacy)) {
        echo "lang/en/{$name}: missing (only in resources)\n";

        continue;
    }
    $a = count(flattenArray(include $enFile));
    $b = count(flattenArray(include $legacy));
    if ($a !== $b) {
        echo "{$name}: resources/en={$a} keys, lang/en={$b} keys (DRIFT)\n";
    }
}
