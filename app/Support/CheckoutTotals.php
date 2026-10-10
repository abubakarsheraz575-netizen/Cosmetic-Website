<?php

namespace App\Support;

use App\Models\Coupon;
use Illuminate\Support\Collection;

class CheckoutTotals
{
    public static function calculate(float $subtotal, float $shipping, ?Coupon $coupon, Collection $taxes): array
    {
        $discount = 0.0;
        if ($coupon) {
            $discount = $coupon->discount_type === 'percent'
                ? round($subtotal * (float) $coupon->discount_value / 100, 2)
                : min($subtotal, (float) $coupon->discount_value);
        }

        $taxable = max(0, round($subtotal - $discount, 2));
        $breakdown = $taxes->map(function ($tax) use ($taxable): array {
            return [
                'id' => $tax->id,
                'name' => $tax->name,
                'percentage' => (float) $tax->percentage,
                'amount' => round($taxable * (float) $tax->percentage / 100, 2),
            ];
        })->values()->all();
        $taxTotal = round(array_sum(array_column($breakdown, 'amount')), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'coupon_code' => $coupon?->code,
            'shipping' => round($shipping, 2),
            'tax_total' => $taxTotal,
            'tax_breakdown' => $breakdown,
            'total' => round($taxable + $taxTotal + $shipping, 2),
        ];
    }
}
