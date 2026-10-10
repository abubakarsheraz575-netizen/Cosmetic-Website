<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Support\MediaLibrary;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MediaManagementController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'folder' => ['nullable', 'string', 'max:500'],
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $disk = Storage::disk('public');
        $folder = trim($validated['folder'] ?? '', '/');
        $directories = $disk->allDirectories();

        abort_if($folder !== '' && ! in_array($folder, $directories, true), 404);

        $folders = collect($disk->directories($folder))->map(function (string $directory) use ($disk): array {
            $files = $disk->allFiles($directory);

            return [
                'name' => basename($directory),
                'path' => $directory,
                'file_count' => count($files),
                'image_count' => collect($files)->filter(fn (string $file): bool => in_array(Str::lower(pathinfo($file, PATHINFO_EXTENSION)), MediaLibrary::IMAGE_EXTENSIONS, true))->count(),
            ];
        });
        $term = trim($validated['q'] ?? '');
        $allFiles = collect($disk->files($folder))
            ->filter(fn (string $file): bool => $term === '' || str_contains(Str::lower(basename($file)), Str::lower($term)))
            ->map(function (string $file) use ($disk): array {
                $extension = Str::lower(pathinfo($file, PATHINFO_EXTENSION));

                return [
                    'name' => basename($file),
                    'path' => $file,
                    'url' => $disk->url($file),
                    'is_image' => in_array($extension, MediaLibrary::IMAGE_EXTENSIONS, true),
                    'type' => $extension !== '' ? Str::upper($extension) : 'FILE',
                    'size' => $disk->size($file),
                    'updated_at' => $disk->lastModified($file),
                ];
            })
            ->sortBy('name')
            ->values();
        $files = new LengthAwarePaginator(
            $allFiles->forPage((int) ($validated['page'] ?? 1), 24)->values(),
            $allFiles->count(),
            24,
            (int) ($validated['page'] ?? 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $usageMap = $this->usageMap($files->getCollection()->where('is_image')->pluck('path')->all());
        $files->getCollection()->transform(function (array $file) use ($usageMap): array {
            $file['used_by'] = $usageMap[$file['path']] ?? [];
            return $file;
        });

        return view('admin.pages.media.index', [
            'folder' => $folder,
            'folders' => $folders,
            'files' => $files,
            'breadcrumbs' => $folder === '' ? [] : collect(explode('/', $folder))->map(function (string $part, int $index) use ($folder): array {
                $parts = explode('/', $folder);

                return ['name' => $part, 'path' => implode('/', array_slice($parts, 0, $index + 1))];
            }),
        ]);
    }

    public function picker(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $term = Str::lower(trim($data['q'] ?? ''));
        $images = collect(Storage::disk('public')->allFiles())
            ->filter(function (string $path) use ($term): bool {
                $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
                return in_array($extension, MediaLibrary::IMAGE_EXTENSIONS, true)
                    && ($term === '' || str_contains(Str::lower(basename($path)), $term));
            })
            ->sort()
            ->take(120)
            ->map(fn (string $path): array => [
                'path' => $path,
                'name' => basename($path),
                'url' => Storage::disk('public')->url($path),
            ])
            ->values();

        return response()->json(['images' => $images]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'folder' => ['nullable', 'string', 'max:500'],
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
        ]);
        $disk = Storage::disk('public');
        $folder = trim($data['folder'] ?? '', '/');
        abort_if($folder !== '' && ! in_array($folder, $disk->allDirectories(), true), 404);

        $stored = [];
        try {
            foreach ($request->file('images') as $image) {
                $path = $image->store($folder !== '' ? $folder : 'media', 'public');
                if (! $path) {
                    throw new RuntimeException('A media image could not be saved. Please try again.');
                }
                $stored[] = $path;
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($stored);
            throw $exception;
        }

        return redirect()->route('admin.media.index', ['folder' => $folder !== '' ? $folder : 'media'])
            ->with('success', count($stored).' image(s) uploaded to the local media library.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $data = $request->validate(['path' => ['required', 'string', 'max:500']]);
        $path = trim($data['path'], '/');
        $disk = Storage::disk('public');
        abort_if($path === '' || str_contains($path, '..') || ! $disk->exists($path), 404);

        $usage = $this->usageMap([$path])[$path] ?? [];
        if ($usage !== []) {
            throw ValidationException::withMessages([
                'path' => 'This image is in use and cannot be deleted: '.implode('; ', $usage),
            ]);
        }
        if (! $disk->delete($path)) {
            throw new RuntimeException('The media image could not be deleted.');
        }

        return redirect()->route('admin.media.index')->with('success', 'Unused media image deleted.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9 _-]*$/'],
            'parent' => ['nullable', 'string', 'max:500'],
        ]);
        $disk = Storage::disk('public');
        $parent = trim($data['parent'] ?? '', '/');
        $directories = $disk->allDirectories();
        abort_if($parent !== '' && ! in_array($parent, $directories, true), 404);

        $folderName = Str::slug($data['name']);
        if ($folderName === '') {
            throw ValidationException::withMessages(['name' => 'Enter a folder name containing letters or numbers.']);
        }
        $path = trim($parent.'/'.$folderName, '/');
        if (in_array($path, $directories, true) || $disk->exists($path)) {
            throw ValidationException::withMessages(['name' => 'A folder with this name already exists here.']);
        }
        if (! $disk->makeDirectory($path)) {
            throw new RuntimeException('The media folder could not be created.');
        }

        return redirect()->route('admin.media.index', $parent !== '' ? ['folder' => $parent] : [])
            ->with('success', 'Media folder created.');
    }

    private function usageMap(array $paths): array
    {
        $usage = array_fill_keys($paths, []);
        if ($paths === []) {
            return $usage;
        }

        foreach (ProductImage::whereIn('path', $paths)->with('product:id,name')->get() as $image) {
            $usage[$image->path][] = 'Product: '.($image->product?->name ?? 'deleted product');
        }
        foreach (ProductVariant::whereIn('image_path', $paths)->with('product:id,name')->get() as $variant) {
            $usage[$variant->image_path][] = 'Product option image: '.($variant->product?->name ?? 'deleted product');
        }
        foreach (Brand::whereIn('logo_path', $paths)->get(['name', 'logo_path']) as $brand) {
            $usage[$brand->logo_path][] = 'Brand logo: '.$brand->name;
        }
        foreach (BlogPost::whereIn('featured_image', $paths)->get(['title', 'featured_image']) as $post) {
            $usage[$post->featured_image][] = 'Blog image: '.$post->title;
        }
        foreach (User::whereIn('profile_photo', $paths)->get(['name', 'profile_photo']) as $user) {
            $usage[$user->profile_photo][] = 'Profile photo: '.$user->name;
        }

        $settings = Setting::query()->get(['key', 'value']);
        foreach ($paths as $path) {
            foreach ($settings as $setting) {
                $value = (string) $setting->value;
                if ($value === $path || str_contains($value, $path)) {
                    $usage[$path][] = 'Store setting: '.$setting->key;
                }
            }
            $usage[$path] = array_values(array_unique($usage[$path]));
        }

        return $usage;
    }
}
