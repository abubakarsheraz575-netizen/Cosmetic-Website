<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['code', 'discount_type', 'discount_value', 'usage_limit', 'uses_count', 'reserved_count', 'starts_at', 'ends_at', 'is_active'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'usage_limit' => 'integer',
            'uses_count' => 'integer',
            'reserved_count' => 'integer',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereRaw('uses_count + reserved_count < usage_limit')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }
}
