<?php

namespace App\Support;

class PaystackSecretKey
{
    public static function normalize(?string $secret): ?string
    {
        $secret = trim((string) $secret);
        $secret = preg_replace('/^(?:authorization\s*:?\s*)?bearer\s+/i', '', $secret) ?? $secret;
        $secret = trim($secret);

        return $secret !== '' ? $secret : null;
    }

    public static function mode(?string $key, string $type): ?string
    {
        $key = trim((string) $key);
        if ($key === '' || preg_match('/\s/', $key)) {
            return null;
        }

        if (preg_match('/^'.preg_quote($type, '/').'\_(test|live)\_.+$/iD', $key, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }
}
