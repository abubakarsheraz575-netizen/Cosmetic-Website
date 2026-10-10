<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaLibrary
{
    public const IMAGE_EXTENSIONS = ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'];
    public const UPLOAD_EXTENSIONS = ['gif', 'jpeg', 'jpg', 'png', 'webp'];

    public static function imagePathRules(): array
    {
        return [
            'nullable',
            'string',
            'max:500',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value !== null && $value !== '' && ! self::isSelectableImage($value)) {
                    $fail('Choose an image that exists in the media library.');
                }
            },
        ];
    }

    public static function isSelectableImage(string $path): bool
    {
        if (str_contains($path, '\\') || str_starts_with($path, '/') || str_contains($path, '..')) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::IMAGE_EXTENSIONS, true)
            && Storage::disk('public')->exists($path)
            && ! Storage::disk('public')->directoryExists($path);
    }

    public static function storeUploadedImage(UploadedFile $file, string $folder): string|false
    {
        return $file->store($folder, 'public');
    }

    /**
     * Find persisted references to an exact public-disk path, including JSON settings.
     *
     * @return array<int, string>
     */
    public static function references(string $path): array
    {
        $references = [];
        $schema = Schema::getFacadeRoot();

        foreach ($schema->getTables() as $tableInfo) {
            $table = is_array($tableInfo) ? $tableInfo['name'] : $tableInfo->name;

            foreach ($schema->getColumns($table) as $columnInfo) {
                $column = is_array($columnInfo) ? $columnInfo['name'] : $columnInfo->name;
                if (! preg_match('/(image|logo|photo|avatar|path|^value$)/i', $column)) {
                    continue;
                }

                $query = DB::table($table)->where($column, $path);
                if ($table === 'settings' && $column === 'value') {
                    $query->orWhere($column, 'like', '%'.$path.'%');
                }

                foreach ($query->limit(100)->get() as $row) {
                    $id = $row->id ?? null;
                    $references[] = $table.'.'.$column.($id !== null ? ' (#'.$id.')' : '');
                }
            }
        }

        return array_values(array_unique($references));
    }

    public static function deleteIfUnused(string|array|null $paths): void
    {
        $disk = Storage::disk('public');
        foreach (array_values(array_unique(array_filter((array) $paths))) as $path) {
            if (self::isReferenced($path) || ! $disk->exists($path)) {
                continue;
            }
            if (! $disk->delete($path)) {
                throw new RuntimeException("The unused media image could not be removed: {$path}");
            }
        }
    }

    private static function isReferenced(string $path): bool
    {
        foreach ([
            ['product_images', 'path'],
            ['product_variants', 'image_path'],
            ['brands', 'logo_path'],
            ['ads', 'image_url'],
            ['ads', 'tablet_image_url'],
            ['blog_posts', 'featured_image'],
            ['users', 'profile_photo'],
        ] as [$table, $column]) {
            if (Schema::hasTable($table)
                && Schema::hasColumn($table, $column)
                && DB::table($table)->where($column, $path)->exists()) {
                return true;
            }
        }

        return Schema::hasTable('settings')
            && Schema::hasColumn('settings', 'value')
            && DB::table('settings')->where('value', $path)
                ->orWhere('value', 'like', '%'.$path.'%')
                ->exists();
    }
}
