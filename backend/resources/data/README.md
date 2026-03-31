# Thai address data

`thailand_subdistricts.min.json` is a merged index built from [kongvut/thai-province-data](https://github.com/kongvut/thai-province-data) (`sub_district.json`, `district.json`, `province.json`) for autocomplete: subdistrict (ตำบล/แขวง), district, province, postal code.

Regenerate (requires network):

```bash
php -r '
$d = json_decode(file_get_contents("https://raw.githubusercontent.com/kongvut/thai-province-data/master/api/latest/district.json"), true);
$p = json_decode(file_get_contents("https://raw.githubusercontent.com/kongvut/thai-province-data/master/api/latest/province.json"), true);
$s = json_decode(file_get_contents("https://raw.githubusercontent.com/kongvut/thai-province-data/master/api/latest/sub_district.json"), true);
$pmap = [];
foreach ($p as $row) { if (($row["deleted_at"] ?? null) === null) { $pmap[$row["id"]] = $row["name_th"]; } }
$dmap = [];
foreach ($d as $row) { if (($row["deleted_at"] ?? null) === null) { $dmap[$row["id"]] = ["n" => $row["name_th"], "pid" => $row["province_id"]]; } }
$out = [];
foreach ($s as $row) {
  if (($row["deleted_at"] ?? null) !== null) { continue; }
  $di = $dmap[$row["district_id"]] ?? null;
  if (! $di) { continue; }
  $pn = $pmap[$di["pid"]] ?? "";
  $out[] = ["t" => $row["name_th"], "a" => $di["n"], "p" => $pn, "z" => str_pad((string) $row["zip_code"], 5, "0", STR_PAD_LEFT), "i" => $row["id"]];
}
file_put_contents(__DIR__."/thailand_subdistricts.min.json", json_encode($out, JSON_UNESCAPED_UNICODE));
echo count($out);
'
```

After replacing the file, clear the app cache or run `php artisan cache:clear` so `ThailandAddressSearchService` reloads the payload.
