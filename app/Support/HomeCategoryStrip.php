<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Collection;

class HomeCategoryStrip
{
    public static function items(): Collection
    {
        $saved = self::overrides();

        return Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query
                ->where('is_active', true)
                ->where('review_status', 'approved'))
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon'])
            ->map(function (Category $category) use ($saved): array {
                $custom = $saved[(string) $category->id] ?? [];

                return [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => trim((string) ($custom['name'] ?? '')) ?: $category->name,
                    'icon' => trim((string) ($custom['icon'] ?? '')) ?: $category->icon,
                    'logo' => ! empty($custom['logo']) ? asset('storage/' . $custom['logo']) : null,
                    'visible' => filter_var($custom['visible'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            });
    }

    public static function overrides(): array
    {
        $saved = json_decode((string) Setting::get('home_category_strip', '[]'), true);

        return is_array($saved) ? $saved : [];
    }

    public static function iconClass(string $icon): string
    {
        if (preg_match('/^ri-[a-z0-9-]+$/', $icon)) {
            return $icon;
        }

        $classes = [];
        foreach (config('product_icons.choices', []) as $choice) {
            if (($choice['value'] ?? null) === $icon) {
                return $choice['class'];
            }
            $classes[$choice['value']] = $choice['class'];
        }

        return $classes[$icon] ?? 'ri-price-tag-3-line';
    }

    public static function usesIconFont(string $icon): bool
    {
        if (preg_match('/^ri-[a-z0-9-]+$/', $icon)) {
            return true;
        }

        foreach (config('product_icons.choices', []) as $choice) {
            if (($choice['value'] ?? null) === $icon) {
                return true;
            }
        }

        return false;
    }
}
