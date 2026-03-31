<?php

namespace App\Support;

use App\Models\Company;

final class StructuredAddressValidation
{
    /** @return array<string, string> */
    public static function rules(): array
    {
        return [
            'address_no' => 'nullable|string|max:50',
            'address_building' => 'nullable|string|max:255',
            'address_soi' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_subdistrict' => 'nullable|string|max:120',
            'address_district' => 'nullable|string|max:120',
            'address_province' => 'nullable|string|max:120',
            'address_postal_code' => 'nullable|string|max:10',
        ];
    }

    /**
     * Rules for web "add branch" form: branch_address_no, branch_address_building, …
     *
     * @return array<string, string>
     */
    public static function rulesForBranchFormPrefix(): array
    {
        $out = [];
        foreach (Company::structuredAddressAttributes() as $key) {
            $out['branch_'.$key] = self::rules()[$key];
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public static function attributeNames(): array
    {
        return [
            'address_no' => __('company.address_no'),
            'address_building' => __('company.address_building'),
            'address_soi' => __('company.address_soi'),
            'address_street' => __('company.address_street'),
            'address_subdistrict' => __('company.address_subdistrict'),
            'address_district' => __('company.address_district'),
            'address_province' => __('company.address_province'),
            'address_postal_code' => __('company.address_postal_code'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributeNamesForBranchFormPrefix(): array
    {
        $names = [];
        foreach (Company::structuredAddressAttributes() as $key) {
            $names['branch_'.$key] = __('company.'.$key);
        }

        return $names;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function onlyStructuredFromBranchPrefixed(array $validated): array
    {
        $out = [];
        foreach (Company::structuredAddressAttributes() as $key) {
            $prefixed = 'branch_'.$key;
            if (array_key_exists($prefixed, $validated)) {
                $out[$key] = $validated[$prefixed];
            }
        }

        return $out;
    }
}
