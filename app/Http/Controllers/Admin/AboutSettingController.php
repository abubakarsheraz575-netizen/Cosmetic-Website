<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Support\HomeCategoryStrip;
use App\Support\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AboutSettingController extends Controller
{
    public function index()
    {
        $aboutSettings = [];
        foreach (config('about_page.content') as $key => $default) {
            $aboutSettings[$key] = Setting::get($key, $default);
        }
        $vProdId = Setting::get('about_product_id');
        $aboutImages = [];
        foreach (array_keys(config('about_page.images')) as $key) {
            $path = Setting::get($key);
            $aboutImages[$key] = $path ? asset('storage/' . $path) : config("about_page.images.{$key}");
        }

        $products = Product::with(['category', 'images'])->orderBy('name')->get(['id', 'name', 'emoji', 'category_id', 'price']);
        $categoryOverrides = HomeCategoryStrip::overrides();
        $productCategoryDisplay = $products->mapWithKeys(function (Product $product) use ($categoryOverrides): array {
            $category = $product->category;
            $custom = $category ? ($categoryOverrides[(string) $category->id] ?? []) : [];
            $icon = trim((string) ($custom['icon'] ?? '')) ?: ($category?->icon ?: ($product->emoji ?: '🧴'));
            $logo = ! empty($custom['logo']) ? asset('storage/' . $custom['logo']) : null;

            return [$product->id => [
                'name' => $category?->name ?? 'Uncategorized',
                'icon' => $icon,
                'icon_class' => HomeCategoryStrip::iconClass($icon),
                'font_icon' => HomeCategoryStrip::usesIconFont($icon),
                'symbol' => str_starts_with($icon, 'ri-') ? ($category?->icon ?: '🧴') : $icon,
                'logo' => $logo,
                'image' => $product->images->first() ? asset('storage/' . $product->images->first()->path) : null,
            ]];
        });

        return view('admin.pages.about-settings', compact('aboutSettings', 'vProdId', 'aboutImages', 'products', 'productCategoryDisplay'));
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (array_keys(config('about_page.content')) as $key) {
            if (str_starts_with($key, 'about_show_')) {
                $rules[$key] = ['sometimes', 'boolean'];
            } elseif (in_array($key, ['stat_years', 'stat_clients', 'stat_products', 'stat_satisfaction'], true)) {
                $rules[$key] = ['nullable', 'numeric', 'min:0', 'max:99999'];
            } else {
                $rules[$key] = ['nullable', 'string', 'max:8000'];
            }
        }
        foreach (['about_product_id' => ['nullable', 'integer', 'exists:products,id'], 'about_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'about_quote_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']] as $key => $rule) {
            $rules[$key] = $rule;
        }
        $data = $request->validate($rules);

        $uploaded = [];
        $previousPaths = [];
        try {
            foreach (['about_image', 'about_quote_image'] as $key) {
                if (! $request->hasFile($key)) {
                    continue;
                }
                $path = $request->file($key)->store('about', 'public');
                if (! $path) {
                    throw new \RuntimeException('An About page image could not be stored.');
                }
                $uploaded[$key] = $path;
                $previous = Setting::get($key);
                if ($previous) {
                    $previousPaths[] = $previous;
                }
            }

            DB::transaction(function () use ($request, $data, $uploaded): void {
                foreach (array_keys(config('about_page.content')) as $key) {
                    if (! array_key_exists($key, $data)) {
                        continue;
                    }
                    $value = str_starts_with($key, 'about_show_')
                        ? (string) (int) ($data[$key] ?? false)
                        : trim((string) $data[$key]);
                    Setting::set($key, $value);
                }
                if ($request->exists('about_product_id')) {
                    Setting::set('about_product_id', $request->filled('about_product_id') ? $data['about_product_id'] : '');
                }
                foreach ($uploaded as $key => $path) {
                    Setting::set($key, $path);
                }
            });
        } catch (\Throwable $exception) {
            if ($uploaded) {
                MediaLibrary::deleteIfUnused(array_values($uploaded));
            }
            throw $exception;
        }

        foreach ($previousPaths as $path) {
            MediaLibrary::deleteIfUnused($path);
        }

        $imgUrl = Setting::get('about_image')
            ? asset('storage/' . Setting::get('about_image'))
            : config('about_page.images.about_image');
        $quoteImgUrl = Setting::get('about_quote_image')
            ? asset('storage/' . Setting::get('about_quote_image'))
            : config('about_page.images.about_quote_image');

        return response()->json([
            'status'  => 'success',
            'message' => 'About page updated successfully!',
            'image'   => $imgUrl,
            'quote_image' => $quoteImgUrl,
        ]);
    }
}