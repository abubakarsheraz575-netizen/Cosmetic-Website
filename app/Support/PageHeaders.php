<?php

namespace App\Support;

use App\Models\Setting;

class PageHeaders
{
    public static function for(string $page): array
    {
        $defaults = config("page_headers.pages.{$page}");

        if (! is_array($defaults)) {
            throw new \InvalidArgumentException("Unknown page header: {$page}");
        }

        $imagePath = Setting::get("page_header_{$page}_image");

        $header = [
            'title' => Setting::get("page_header_{$page}_title", $defaults['title']),
            'image' => $imagePath
                ? asset('storage/' . $imagePath)
                : $defaults['image'],
        ];
        if (isset($defaults['description'])) {
            $header['description'] = Setting::get("page_header_{$page}_description", $defaults['description']);
        }

        return $header;
    }
}
