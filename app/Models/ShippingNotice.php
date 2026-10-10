<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingNotice extends Model
{
    protected $fillable = ['user_id', 'order_id', 'title', 'message', 'type', 'is_active'];

    /* Global notices (sab users ko) */
    public static function active()
    {
        return self::whereNull('user_id')->where('is_active', true)->latest()->get();
    }

    /* ⭐ User ke dashboard ke liye — global + uske personal notices */
    public static function forUser($userId)
    {
        return self::where('is_active', true)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->latest()
            ->get();
    }
}