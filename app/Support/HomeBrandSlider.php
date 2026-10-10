<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnexpectedValueException;

class HomeBrandSlider
{
    public static function items(): Collection
    {
        $saved = Setting::get('home_brand_slider');
        if ($saved !== null) {
            $items = json_decode($saved, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($items)) {
                throw new UnexpectedValueException('The saved homepage brand slider must be a list.');
            }

            return collect($items)->map(fn (array $item): array => [
                'id' => (string) ($item['id'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
                'mark' => (string) ($item['mark'] ?? ''),
                'logo_path' => $item['logo_path'] ?? null,
                'logo' => ! empty($item['logo_path']) ? asset('storage/' . $item['logo_path']) : null,
                'logo_owned' => (bool) ($item['logo_owned'] ?? false),
                'visible' => (bool) ($item['visible'] ?? false),
            ])->values();
        }

        $items = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'logo_path'])
            ->map(fn (Brand $brand): array => [
                'id' => 'brand-' . $brand->id,
                'name' => $brand->name,
                'mark' => self::mark($brand->name),
                'logo_path' => $brand->logo_path,
                'logo' => $brand->logo_path ? asset('storage/' . $brand->logo_path) : null,
                'logo_owned' => false,
                'visible' => true,
            ]);

        $names = $items->map(fn (array $item): string => self::normalizedName($item['name']));
        $showcase = collect(config('homepage.featured_brands', []))
            ->reject(fn (array $brand): bool => $names->contains(self::normalizedName($brand['name'])))
            ->map(fn (array $brand): array => [
                'id' => 'featured-' . Str::slug($brand['name']),
                'name' => $brand['name'],
                'mark' => $brand['mark'] ?? self::mark($brand['name']),
                'logo_path' => null,
                'logo' => null,
                'logo_owned' => false,
                'visible' => true,
            ]);

        return $items->concat($showcase)->values();
    }

    public static function visibleItems(): Collection
    {
        return self::items()->where('visible', true)->values();
    }

    public static function mark(string $name): string
    {
        return mb_strtoupper(mb_substr(trim($name), 0, 2));
    }

    private static function normalizedName(string $name): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]/i', '', $name));
    }
}
