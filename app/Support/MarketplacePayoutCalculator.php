<?php

namespace App\Support;

use App\Models\Order;
use App\Models\User;

class MarketplacePayoutCalculator
{
    public static function forVendor(Order $order, User $vendor, float $commissionRate): ?array
    {
        $items = $order->items->filter(
            fn ($item) => $item->product?->submitted_by === $vendor->id
        )->values();

        if ($items->isEmpty()) {
            return null;
        }

        $grossSales = round((float) $items->sum(fn ($item) => (float) $item->line_total), 2);
        $orderSubtotal = (float) $order->subtotal;
        $couponDiscountShare = $orderSubtotal > 0
            ? min($grossSales, round((float) $order->discount * ($grossSales / $orderSubtotal), 2))
            : 0.0;
        $salesAmount = round(max(0, $grossSales - $couponDiscountShare), 2);
        $commissionAmount = round($salesAmount * $commissionRate / 100, 2);

        return [
            'items' => $items,
            'gross_sales' => $grossSales,
            'coupon_discount_share' => $couponDiscountShare,
            'sales_amount' => $salesAmount,
            'commission_rate' => round($commissionRate, 2),
            'commission_amount' => $commissionAmount,
            'payout_amount' => round(max(0, $salesAmount - $commissionAmount), 2),
        ];
    }
}
