<?php

namespace App\Models\Concerns;

trait HasStructuredAddress
{
    /** @return list<string> */
    public static function structuredAddressAttributes(): array
    {
        return [
            'address_no',
            'address_building',
            'address_soi',
            'address_street',
            'address_subdistrict',
            'address_district',
            'address_province',
            'address_postal_code',
        ];
    }

    /** @return list<string> */
    public static function structuredPhysicalLineAttributes(): array
    {
        return [
            'address_no',
            'address_building',
            'address_soi',
            'address_street',
        ];
    }

    /** @return list<string> */
    public static function structuredAdminLineAttributes(): array
    {
        return [
            'address_subdistrict',
            'address_district',
            'address_province',
            'address_postal_code',
        ];
    }

    public function hasStructuredAddressParts(): bool
    {
        foreach (static::structuredAddressAttributes() as $attr) {
            if (filled($this->{$attr})) {
                return true;
            }
        }

        return false;
    }

    /**
     * Human-readable address: structured lines if any part set, else legacy `address`.
     */
    public function formattedAddress(): string
    {
        if (! $this->hasStructuredAddressParts()) {
            return trim((string) ($this->address ?? ''));
        }

        $line1 = collect(static::structuredPhysicalLineAttributes())
            ->map(fn (string $k) => $this->{$k})
            ->filter()
            ->implode(' ');

        $line2 = collect(static::structuredAdminLineAttributes())
            ->map(fn (string $k) => $this->{$k})
            ->filter()
            ->implode(' ');

        return trim($line1.($line2 !== '' ? "\n".$line2 : ''));
    }

    /**
     * Single-line text for legacy `address` column (export / APIs that read only `address`).
     */
    public static function composeStructuredAddressText(self $model): string
    {
        $parts = [];
        foreach (static::structuredAddressAttributes() as $attr) {
            $v = trim((string) ($model->getAttribute($attr) ?? ''));
            if ($v !== '') {
                $parts[] = $v;
            }
        }

        return implode(' ', $parts);
    }

    protected static function bootHasStructuredAddress(): void
    {
        static::saving(function ($model) {
            if ($model->hasStructuredAddressParts()) {
                $model->setAttribute('address', static::composeStructuredAddressText($model));
            }
        });
    }
}
