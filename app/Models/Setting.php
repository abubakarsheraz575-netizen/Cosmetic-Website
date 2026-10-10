<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function marketplaceCommissionRate(): float
    {
        $rate = self::get('marketplace_commission_rate', '10');

        return is_numeric($rate) ? min(100, max(0, (float) $rate)) : 10.0;
    }

    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}