<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\HomeCategoryStrip;
use App\Support\HomeBrandSlider;
use App\Support\SafeSvgIcon;
use App\Support\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HomeSettingController extends Controller
{
    public function slider()
    {
        $brands = HomeBrandSlider::items();

        return view('admin.pages.home-slider', compact('brands'));
    }

    public function updateSlider(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1', 'max:30'],
            'order.*' => ['required', 'string', 'max:100', 'distinct'],
            'brands' => ['required', 'array', 'min:1', 'max:30'],
            'brands.*.name' => ['required', 'string', 'max:120'],
            'brands.*.mark' => ['nullable', 'string', 'max:12'],
            'brands.*.visible' => ['sometimes', 'boolean'],
            'brands.*.remove_logo' => ['sometimes', 'boolean'],
            'brands.*.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brands.*.logo_path' => MediaLibrary::imagePathRules(),
        ]);

        $order = array_values($data['order']);
        $brandIds = array_keys($data['brands']);
        if (count($order) !== count($brandIds) || array_diff($order, $brandIds) !== []) {
            throw ValidationException::withMessages([
                'order' => 'The slider order is invalid. Refresh the page and try again.',
            ]);
        }

        $previous = HomeBrandSlider::items()->keyBy('id');
        $items = [];
        $uploadedPaths = [];
        $pathsToDelete = [];

        try {
            foreach ($order as $id) {
                $input = $data['brands'][$id];
                $old = $previous->get($id);
                $newLogo = $request->file("brands.{$id}.logo");
                $selectedLogo = $input['logo_path'] ?? null;
                $removeLogo = (bool) ($input['remove_logo'] ?? false);
                $logoPath = $old['logo_path'] ?? null;
                $logoOwned = $old['logo_owned'] ?? false;

                if ($newLogo) {
                    $logoPath = $newLogo->store('home/brand-logos', 'public');
                    if (! $logoPath) {
                        throw new \RuntimeException('A brand slider logo could not be stored.');
                    }
                    $uploadedPaths[] = $logoPath;
                    if ($logoOwned && $old['logo_path']) {
                        $pathsToDelete[] = $old['logo_path'];
                    }
                    $logoOwned = true;
                } elseif ($selectedLogo) {
                    if ($logoOwned && $old['logo_path'] && $old['logo_path'] !== $selectedLogo) {
                        $pathsToDelete[] = $old['logo_path'];
                    }
                    $logoPath = $selectedLogo;
                    $logoOwned = false;
                } elseif ($removeLogo) {
                    if ($logoOwned && $logoPath) {
                        $pathsToDelete[] = $logoPath;
                    }
                    $logoPath = null;
                    $logoOwned = false;
                }

                $name = trim($input['name']);
                $items[] = [
                    'id' => $id,
                    'name' => $name,
                    'mark' => trim((string) ($input['mark'] ?? '')) ?: HomeBrandSlider::mark($name),
                    'logo_path' => $logoPath,
                    'logo_owned' => $logoOwned,
                    'visible' => (bool) ($input['visible'] ?? false),
                ];
            }

            DB::transaction(fn () => Setting::set(
                'home_brand_slider',
                json_encode($items, JSON_THROW_ON_ERROR)
            ));
        } catch (\Throwable $exception) {
            if ($uploadedPaths !== []) {
                MediaLibrary::deleteIfUnused($uploadedPaths);
            }

            throw $exception;
        }

        if ($pathsToDelete !== []) {
            MediaLibrary::deleteIfUnused(array_values(array_unique($pathsToDelete)));
        }

        return redirect()->route('admin.home-slider')->with('success', 'Homepage slider saved.');
    }

    public function index()
    {
        $settings = [];
        foreach (config('homepage.content') as $key => $default) {
            $settings[$key] = Setting::get($key, $default);
        }
        foreach (config('homepage.images') as $key => $default) {
            $path = Setting::get($key);
            $settings[$key] = $path ? asset('storage/' . $path) : $default;
        }

        $homeCategories = HomeCategoryStrip::items();

        return view('admin.pages.home-settings', compact('settings', 'homeCategories'));
    }

    public function update(Request $request): JsonResponse
    {
        $rules = [];
        foreach (array_keys(config('homepage.content')) as $key) {
            $rules[$key] = str_starts_with($key, 'home_show_')
                ? ['sometimes', 'boolean']
                : ['nullable', 'string', 'max:2000'];
        }
        foreach (array_keys(config('homepage.images')) as $key) {
            $rules[$key] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            $rules[$key.'_path'] = MediaLibrary::imagePathRules();
        }
        $rules['home_category_strip'] = ['sometimes', 'array', 'max:50'];
        $rules['home_category_strip.*.name'] = ['required', 'string', 'max:100'];
        $rules['home_category_strip.*.icon'] = ['nullable', 'string', 'max:100'];
        $rules['home_category_strip.*.visible'] = ['sometimes', 'boolean'];
        $rules['home_category_strip.*.clear_logo'] = ['sometimes', 'boolean'];
        $rules['home_category_strip.*.logo_path'] = MediaLibrary::imagePathRules();
        $rules['home_category_strip.*.logo'] = [
            'nullable',
            'file',
            'max:2048',
            function (string $attribute, mixed $file, \Closure $fail): void {
                $extension = strtolower($file->getClientOriginalExtension());
                $mime = $file->getMimeType();
                $allowed = [
                    'jpg' => ['image/jpeg'],
                    'jpeg' => ['image/jpeg'],
                    'png' => ['image/png'],
                    'webp' => ['image/webp'],
                    'svg' => ['image/svg+xml', 'text/plain', 'application/xml', 'text/xml'],
                ];
                if (! isset($allowed[$extension]) || ! in_array($mime, $allowed[$extension], true)) {
                    $fail('Upload a JPG, PNG, WebP or SVG logo.');
                    return;
                }
                if ($extension === 'svg') {
                    try {
                        SafeSvgIcon::sanitize($file->get());
                    } catch (\InvalidArgumentException $exception) {
                        $fail($exception->getMessage());
                    }
                }
            },
        ];
        $data = $request->validate($rules);

        $uploaded = [];
        $uploadedLogos = [];
        $storedUploads = [];
        $storedLogoUploads = [];
        $previousPaths = [];
        try {
            foreach (array_keys(config('homepage.images')) as $key) {
                if ($request->hasFile($key)) {
                    $previous = Setting::get($key);
                    $path = $request->file($key)->store('home', 'public');
                    if (! $path) {
                        throw new \RuntimeException('A home page image could not be stored.');
                    }
                    $uploaded[$key] = $path;
                    $storedUploads[] = $path;
                    if ($previous) {
                        $previousPaths[] = $previous;
                    }
                } elseif (! empty($data[$key.'_path'])) {
                    $path = $data[$key.'_path'];
                    $previous = Setting::get($key);
                    $uploaded[$key] = $path;
                    if ($previous && $previous !== $path) {
                        $previousPaths[] = $previous;
                    }
                }
            }
            $existingStrip = HomeCategoryStrip::overrides();
            foreach (($data['home_category_strip'] ?? []) as $categoryId => $item) {
                $logoFile = $item['logo'] ?? null;
                $selectedLogo = $item['logo_path'] ?? null;
                $previousLogo = $existingStrip[(string) $categoryId]['logo'] ?? null;
                if ($previousLogo && ($logoFile || $selectedLogo || ($item['clear_logo'] ?? false))) {
                    $previousPaths[] = $previousLogo;
                }
                if (! $logoFile) {
                    if ($selectedLogo) {
                        $uploadedLogos[(string) $categoryId] = $selectedLogo;
                    }
                    continue;
                }
                if (strtolower($logoFile->getClientOriginalExtension()) === 'svg') {
                    $safeSvg = SafeSvgIcon::sanitize($logoFile->get());
                    $path = 'home/category-logos/' . Str::uuid() . '.svg';
                    if (! Storage::disk('public')->put($path, $safeSvg)) {
                        throw new \RuntimeException('A category SVG logo could not be stored.');
                    }
                } else {
                    $path = $logoFile->store('home/category-logos', 'public');
                    if (! $path) {
                        throw new \RuntimeException('A category logo could not be stored.');
                    }
                }
                $uploadedLogos[(string) $categoryId] = $path;
                $storedLogoUploads[] = $path;
            }

            DB::transaction(function () use ($data, $uploaded, $uploadedLogos, $existingStrip): void {
                foreach (array_keys(config('homepage.content')) as $key) {
                    $value = str_starts_with($key, 'home_show_')
                        ? (string) (int) ($data[$key] ?? false)
                        : trim((string) ($data[$key] ?? ''));
                    Setting::set($key, $value);
                }
                foreach ($uploaded as $key => $path) {
                    Setting::set($key, $path);
                }
                if (array_key_exists('home_category_strip', $data)) {
                    $strip = [];
                    foreach ($data['home_category_strip'] as $categoryId => $item) {
                        $previousLogo = $existingStrip[(string) $categoryId]['logo'] ?? '';
                        $logo = $uploadedLogos[(string) $categoryId] ?? (
                            ($item['clear_logo'] ?? false) ? '' : $previousLogo
                        );
                        $strip[(string) $categoryId] = [
                            'name' => trim($item['name']),
                            'icon' => trim((string) ($item['icon'] ?? '')),
                            'logo' => $logo,
                            'visible' => (string) (int) ($item['visible'] ?? false),
                        ];
                    }
                    Setting::set('home_category_strip', json_encode($strip, JSON_THROW_ON_ERROR));
                }
            });
        } catch (\Throwable $exception) {
            if ($storedUploads || $storedLogoUploads) {
                MediaLibrary::deleteIfUnused(array_merge($storedUploads, $storedLogoUploads));
            }

            throw $exception;
        }

        foreach ($previousPaths as $path) {
            MediaLibrary::deleteIfUnused($path);
        }

        $images = [];
        foreach (array_keys(config('homepage.images')) as $key) {
            $path = Setting::get($key);
            $images[$key] = $path ? asset('storage/' . $path) : config('homepage.images.' . $key);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Home page content saved successfully.',
            'images' => $images,
            'category_logos' => HomeCategoryStrip::items()->mapWithKeys(fn (array $category): array => [
                (string) $category['id'] => $category['logo'],
            ]),
        ]);
    }
}
