<?php

namespace App\Models;

use App\Support\MediaLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ad extends Model
{
    protected $fillable = [
        'created_by', 'name', 'shortcode', 'title', 'subtitle', 'button_label',
        'target_url', 'image_url', 'tablet_image_url', 'opens_new_tab',
        'sort_order', 'status', 'clicks_count', 'starts_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'opens_new_tab' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $dates) => $dates->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $dates) => $dates->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function imageSource(string $field): ?string
    {
        $source = match ($field) {
            'image_url' => $this->image_url,
            'tablet_image_url' => $this->tablet_image_url,
            default => null,
        };

        if (! is_string($source) || $source === '') {
            return null;
        }

        if (MediaLibrary::isSelectableImage($source)) {
            return Storage::disk('public')->url($source);
        }

        return filter_var($source, FILTER_VALIDATE_URL)
            && parse_url($source, PHP_URL_SCHEME) === 'https'
                ? $source
                : null;
    }
}
