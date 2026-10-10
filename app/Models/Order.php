<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id','order_number','name','email','phone','address','city','postal_code','country','subtotal','discount','coupon_code','coupon_discount_type','coupon_discount_value','shipping','tax_total','tax_breakdown','total','payment_method','payment_status','status','transaction_id','notes','paid_at','is_manual_payment','payment_note','payment_recorded_by'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'coupon_discount_value' => 'decimal:2',
            'shipping' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'tax_breakdown' => 'array',
            'total'    => 'decimal:2',
            'paid_at' => 'datetime',
            'is_manual_payment' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function couponRedemption()
    {
        return $this->hasOne(CouponRedemption::class);
    }
}