<?php

namespace App\Support;

class InternalBarcode
{
    public static function forProductId(int $id): string
    {
        return self::ean13('20', $id);
    }

    public static function forVariantId(int $id): string
    {
        return self::ean13('21', $id);
    }

    private static function ean13(string $prefix, int $id): string
    {
        $body = $prefix.str_pad((string) $id, 10, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += (int) $body[$index] * ($index % 2 === 0 ? 1 : 3);
        }

        return $body.((10 - ($sum % 10)) % 10);
    }
}
