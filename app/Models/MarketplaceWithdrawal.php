<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceWithdrawal extends Model
{
    protected $fillable = [
        'vendor_id', 'order_id', 'order_number', 'payment_transaction_id', 'order_paid_at',
        'sales_amount', 'coupon_discount_share', 'commission_rate', 'commission_amount',
        'amount', 'payout_method', 'payout_details', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sales_amount' => 'decimal:2',
            'coupon_discount_share' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'order_paid_at' => 'datetime',
            'payout_details' => 'encrypted',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
