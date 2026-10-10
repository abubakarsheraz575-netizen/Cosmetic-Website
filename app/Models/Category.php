<?php

namespace App\Models;

use App\Support\HomeCategoryStrip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'slug', 'icon', 'description', 'is_active'])]
class Category extends Model
{
    protected $attributes = [
        'icon' => 'ri-price-tag-3-line',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function specificationAttributes()
    {
        return $this->hasMany(SpecificationAttribute::class)->orderBy('sort_order')->orderBy('name');
    }

    public function iconClass(): string
    {
        $icon = trim((string) $this->icon);

        if ($icon !== '' && collect(config('category_icons.choices'))->contains('value', $icon)) {
            return $icon;
        }

        return HomeCategoryStrip::iconClass($icon ?: config('category_icons.fallback'));
    }
}