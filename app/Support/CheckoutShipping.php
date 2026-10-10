<?php

namespace App\Support;

class CheckoutShipping
{
    public static function weightInKilograms(?float $weight, string $unit): float
    {
        $kilogramsPerUnit = match ($unit) {
            'mg' => 0.000001,
            'g' => 0.001,
            'kg' => 1.0,
            'oz' => 0.028349523125,
            'lb' => 0.45359237,
            default => 0.001,
        };

        return max(0, $weight ?? 0) * $kilogramsPerUnit;
    }

    public static function calculate(float $subtotal, float $weightKg, array $settings): array
    {
        $base = $subtotal >= $settings['free_over'] ? 0.0 : $settings['cost'];
        $excessKg = max(0, $weightKg - $settings['included_weight_kg']);
        $billableExtraKg = (int) ceil(max(0, $excessKg - 0.000000001));
        $weightSurcharge = round($billableExtraKg * $settings['surcharge_per_kg'], 2);

        return [
            'base' => round($base, 2),
            'weight_surcharge' => $weightSurcharge,
            'billable_extra_kg' => $billableExtraKg,
            'weight_kg' => round($weightKg, 3),
            'total' => round($base + $weightSurcharge, 2),
        ];
    }
}
