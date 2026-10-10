<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use App\Models\ProductLabel;
use App\Models\ProductSkuVariant;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Support\InternalBarcode;
use App\Support\MediaLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'size', 'brand', 'images', 'skuVariants'])->latest()->get();

        $stats = [
            'total'    => Product::count(),
            'active'   => Product::where('is_active', true)->count(),
            'discount' => Product::where(function ($query) {
                $query->where('discount', '>', 0)->orWhereNotNull('sale_price');
            })->count(),
            'stockout' => Product::where('stock', 0)->count(),
        ];

        $categories = Category::orderBy('name')->get();
        $sizes      = Size::orderBy('ml')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.pages.products', compact('products', 'stats', 'categories', 'sizes', 'brands'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $sizes      = Size::where('is_active', true)->orderBy('ml')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $attributes = ProductAttribute::withCount('variants')->orderBy('name')->get();
        $collections = ProductCollection::withCount('products')->orderBy('name')->get();
        $labels = ProductLabel::orderBy('type')->orderBy('name')->get();

        return view('admin.pages.product-create', compact('categories', 'sizes', 'brands', 'attributes', 'collections', 'labels'));
    }

    private function validated(Request $request): array
    {
        $discountRules = $request->input('discount_type', 'percent') === 'fixed'
            ? ['nullable', 'numeric', 'min:0', 'lte:price']
            : ['nullable', 'numeric', 'min:0', 'max:100'];
        $saleDiscountRules = $request->input('sale_discount_type', 'percent') === 'fixed'
            ? ['nullable', 'numeric', 'min:0', 'lte:price']
            : ['nullable', 'numeric', 'min:0', 'max:100'];

        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id'    => ['nullable', 'integer', Rule::exists('brands', 'id')->where('is_active', true)],
            'size_id'     => ['nullable', 'exists:sizes,id'],
            'price'       => ['required', 'numeric', 'min:0'],
            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                'lt:price',
                Rule::requiredIf(fn (): bool => ($request->filled('sale_starts_at') || $request->filled('sale_ends_at'))
                    && ! $request->filled('sale_discount')),
            ],
            'sale_discount' => [
                ...$saleDiscountRules,
                Rule::requiredIf(fn (): bool => ($request->filled('sale_starts_at') || $request->filled('sale_ends_at'))
                    && ! $request->filled('sale_price')),
            ],
            'sale_discount_type' => ['nullable', 'required_with:sale_discount', Rule::in(['percent', 'fixed'])],
            'sale_starts_at' => ['nullable', 'date', 'required_with:sale_price,sale_discount,sale_ends_at'],
            'sale_ends_at' => ['nullable', 'date', 'after:sale_starts_at', 'required_with:sale_price,sale_discount,sale_starts_at'],
            'discount_type' => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount'    => $discountRules,
            'stock'       => ['required_without:sku_variants', 'integer', 'min:0'],
            'emoji'       => ['nullable', 'string', 'max:8'],
            'description' => ['nullable', 'string', 'max:1000'],
            'shipping_weight' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'weight_unit' => ['nullable', Rule::in(['mg', 'g', 'kg', 'lb', 'oz'])],
            'shipping_length' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'shipping_width' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'shipping_height' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'dimension_unit' => ['nullable', Rule::in(['mm', 'cm', 'm', 'in'])],
            'labels' => ['nullable', 'array', 'max:20'],
            'labels.*' => ['integer', 'distinct', 'exists:product_labels,id'],
            'label_types' => ['nullable', 'array', 'max:3'],
            'label_types.*' => ['required', 'string', 'distinct', Rule::in(ProductLabel::TYPES)],
            'is_collection' => ['sometimes', 'boolean'],
            'collections' => ['exclude_unless:is_collection,1', 'required', 'array', 'min:1', 'max:20'],
            'collections.*' => ['required', 'integer', 'distinct', 'exists:product_collections,id'],
            'images'      => ['nullable', 'array', 'max:6'],
            'images.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_paths' => ['nullable', 'array', 'max:6'],
            'image_paths.*' => ['required', 'string', 'max:500', 'distinct', 'regex:/^(?!\/)(?!.*(?:^|\/)\.\.(?:\/|$))[A-Za-z0-9._\/-]+$/'],
            'cover_image_index' => ['nullable', 'required_with:images,image_paths', 'integer', 'min:0'],
            'variants' => ['nullable', 'array', 'max:100'],
            'variants.*.product_attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'variants.*.value' => ['required', 'string', 'max:120'],
            'variants.*.description' => ['nullable', 'string', 'max:500'],
            'variants.*.color_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'variants.*.image_path' => MediaLibrary::imagePathRules(),
            'sku_variants' => ['nullable', 'array', 'max:500'],
            'sku_variants.*.stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'sku_variants.*.options' => ['required', 'array', 'min:1', 'max:10'],
            'sku_variants.*.options.*.product_attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'sku_variants.*.options.*.value' => ['required', 'string', 'max:120'],
        ]);
    }

    public function quickStoreBrand(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:brands,name'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $logoPath = $request->file('logo')?->store('brands', 'public');
        if ($request->hasFile('logo') && ! $logoPath) {
            throw new \RuntimeException('The brand logo could not be saved. Please try again.');
        }

        try {
            $brand = Brand::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name'], 'brands'),
                'logo_path' => $logoPath,
                'is_active' => true,
            ]);
        } catch (Throwable $exception) {
            if ($logoPath) {
                MediaLibrary::deleteIfUnused($logoPath);
            }
            throw $exception;
        }

        return response()->json([
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }

    public function quickStoreAttribute(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:product_attributes,name'],
            'type' => ['required', Rule::in(['color', 'text', 'size'])],
        ]);
        $attribute = ProductAttribute::create($data);

        return response()->json([
            'attribute' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'type' => $attribute->type,
            ],
        ]);
    }

    private function uniqueSlug(string $value, string $table): string
    {
        $base = Str::slug($value) ?: 'brand';
        $slug = $base;
        $suffix = 2;

        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
    public function show(Product $product)
{
    $product->load(['category', 'size', 'images']);

    return view('admin.pages.product-show', compact('product'));
}

    public function edit(Product $product)
    {
        $product->load(['category', 'brand', 'size', 'images']);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $sizes = Size::where('is_active', true)->orderBy('ml')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.pages.product-edit', compact('product', 'categories', 'sizes', 'brands'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $imageFiles = $request->file('images', []);
        $mediaPaths = array_values(array_unique($data['image_paths'] ?? []));
        if (count($imageFiles) + count($mediaPaths) > 6) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image_paths' => ['Choose no more than six product photos.'],
            ]);
        }
        foreach ($mediaPaths as $path) {
            if (! Storage::disk('public')->exists($path)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'image_paths' => ['A selected Media library image is no longer available. Refresh and choose it again.'],
                ]);
            }
        }
        if (($imageFiles !== [] || $mediaPaths !== [])
            && (! isset($data['cover_image_index']) || (int) $data['cover_image_index'] >= count($imageFiles) + count($mediaPaths))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cover_image_index' => ['Choose one of the selected photos as the storefront photo.'],
            ]);
        }
        $data['variants'] = collect($data['variants'] ?? [])->values();
        $data['sku_variants'] = collect($data['sku_variants'] ?? [])->values();
        $variantKeys = [];
        $optionLookup = [];
        $attributeIds = [];
        foreach ($data['variants'] as $variant) {
            $attributeId = (int) $variant['product_attribute_id'];
            $value = trim($variant['value']);
            $variantKey = $attributeId.'|'.mb_strtolower($value);
            if (isset($variantKeys[$variantKey])) {
                return response()->json([
                    'message' => 'Option values must be unique for each attribute.',
                    'errors' => ['variants' => ['Option values must be unique for each attribute.']],
                ], 422);
            }
            $variantKeys[$variantKey] = true;
            $attributeIds[$attributeId] = true;
            $optionLookup[$variantKey] = [
                'product_attribute_id' => $attributeId,
                'value' => $value,
                'color_hex' => $variant['color_hex'] ?? null,
            ];
        }

        $combinationKeys = [];
        foreach ($data['sku_variants'] as $skuVariant) {
            $combination = [];
            $chosenAttributes = [];
            foreach ($skuVariant['options'] as $option) {
                $attributeId = (int) $option['product_attribute_id'];
                $optionKey = $attributeId.'|'.mb_strtolower(trim($option['value']));
                if (isset($chosenAttributes[$attributeId]) || ! isset($optionLookup[$optionKey])) {
                    return response()->json([
                        'message' => 'Each sellable variant must use one valid value from every product option.',
                        'errors' => ['sku_variants' => ['Each sellable variant must use one valid value from every product option.']],
                    ], 422);
                }
                $chosenAttributes[$attributeId] = true;
                $combination[] = $optionKey;
            }
            sort($combination);
            $chosenIds = array_keys($chosenAttributes);
            $allIds = array_keys($attributeIds);
            sort($chosenIds);
            sort($allIds);
            if ($chosenIds !== $allIds) {
                return response()->json([
                    'message' => 'Each sellable variant must select one value for every option.',
                    'errors' => ['sku_variants' => ['Each sellable variant must select one value for every option.']],
                ], 422);
            }
            $combinationKey = implode(';', $combination);
            if (isset($combinationKeys[$combinationKey])) {
                return response()->json([
                    'message' => 'Sellable product variants must have unique option combinations.',
                    'errors' => ['sku_variants' => ['Sellable product variants must have unique option combinations.']],
                ], 422);
            }
            $combinationKeys[$combinationKey] = true;
        }
        if ($data['variants']->isNotEmpty() && $data['sku_variants']->isEmpty()) {
            return response()->json([
                'message' => 'Add stock and barcode-ready sellable combinations for the selected options.',
                'errors' => ['sku_variants' => ['Add at least one sellable combination for the selected options.']],
            ], 422);
        }
        if ($data['variants']->isEmpty() && $data['sku_variants']->isNotEmpty()) {
            return response()->json([
                'message' => 'Add product option values before creating sellable combinations.',
                'errors' => ['sku_variants' => ['Add product option values before creating sellable combinations.']],
            ], 422);
        }

        $storedPaths = [];
        try {
            $product = DB::transaction(function () use ($data, $request, $mediaPaths, &$storedPaths) {
                $saleStartsAt = isset($data['sale_starts_at']) ? \Illuminate\Support\Carbon::parse($data['sale_starts_at']) : null;
                $saleEndsAt = isset($data['sale_ends_at']) ? \Illuminate\Support\Carbon::parse($data['sale_ends_at']) : null;
                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
                    'description' => $data['description'] ?? null,
                    'category_id' => $data['category_id'],
                    'brand_id' => $data['brand_id'] ?? null,
                    'size_id' => $data['size_id'] ?? null,
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'sale_discount' => $data['sale_discount'] ?? null,
                    'sale_discount_type' => $data['sale_discount_type'] ?? 'percent',
                    'sale_starts_at' => $saleStartsAt,
                    'sale_ends_at' => $saleEndsAt,
                    'discount' => $data['discount'] ?? 0,
                    'discount_type' => $data['discount_type'] ?? 'percent',
                    'stock' => $data['sku_variants']->isNotEmpty() ? 0 : (int) ($data['stock'] ?? 0),
                    'emoji' => $data['emoji'] ?? '🧴',
                    'shipping_weight' => $data['shipping_weight'] ?? null,
                    'weight_unit' => $data['weight_unit'] ?? 'g',
                    'shipping_length' => $data['shipping_length'] ?? null,
                    'shipping_width' => $data['shipping_width'] ?? null,
                    'shipping_height' => $data['shipping_height'] ?? null,
                    'dimension_unit' => $data['dimension_unit'] ?? 'cm',
                    'is_active' => $request->boolean('is_active'),
                ]);
                $product->update([
                    'sku' => 'COS-'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT),
                    'barcode' => InternalBarcode::forProductId((int) $product->id),
                ]);

                $coverIndex = isset($data['cover_image_index']) ? (int) $data['cover_image_index'] : null;
                foreach ($request->file('images', []) as $i => $file) {
                    $path = $file->store('products', 'public');
                    if (! $path) {
                        throw new \RuntimeException('A product image could not be saved. Please try again.');
                    }
                    $storedPaths[] = $path;
                    $product->images()->create([
                        'path' => $path,
                        'sort_order' => $i,
                        'is_cover' => $coverIndex === $i,
                    ]);
                }
                foreach ($mediaPaths as $i => $path) {
                    $product->images()->create([
                        'path' => $path,
                        'sort_order' => count($request->file('images', [])) + $i,
                        'is_cover' => $coverIndex === count($request->file('images', [])) + $i,
                    ]);
                }

                $savedOptions = [];
                foreach ($data['variants'] as $index => $variant) {
                    $imagePath = null;
                    if (isset($variant['image'])) {
                        $imagePath = $variant['image']->store('product-variants', 'public');
                        if (! $imagePath) {
                            throw new \RuntimeException('A variant image could not be saved. Please try again.');
                        }
                        $storedPaths[] = $imagePath;
                    } elseif (! empty($variant['image_path'])) {
                        $imagePath = $variant['image_path'];
                    }

                    $option = ProductVariant::create([
                        'product_id' => $product->id,
                        'product_attribute_id' => $variant['product_attribute_id'],
                        'value' => trim($variant['value']),
                        'description' => $variant['description'] ?? null,
                        'color_hex' => $variant['color_hex'] ?? null,
                        'image_path' => $imagePath,
                    ]);
                    $savedOptions[(int) $variant['product_attribute_id'].'|'.mb_strtolower(trim($variant['value']))] = $option;
                }

                $stockTotal = 0;
                foreach ($data['sku_variants'] as $index => $skuVariant) {
                    $sku = ProductSkuVariant::create([
                        'product_id' => $product->id,
                        'sku' => $product->sku.'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                        'barcode' => 'TMP'.Str::upper(Str::random(16)),
                        'stock' => (int) $skuVariant['stock'],
                    ]);
                    $sku->update(['barcode' => InternalBarcode::forVariantId((int) $sku->id)]);
                    $stockTotal += (int) $skuVariant['stock'];
                    $optionIds = collect($skuVariant['options'])
                        ->map(fn ($option) => $savedOptions[(int) $option['product_attribute_id'].'|'.mb_strtolower(trim($option['value']))]->id)
                        ->all();
                    $sku->options()->sync($optionIds);
                }
                if ($data['sku_variants']->isNotEmpty()) {
                    $product->update(['stock' => $stockTotal]);
                }

                $labelIds = $data['labels'] ?? [];
                foreach ($data['label_types'] ?? [] as $type) {
                    $labelIds[] = ProductLabel::firstOrCreate([
                        'name' => ucfirst($type),
                        'type' => $type,
                    ])->id;
                }
                if ($labelIds !== []) {
                    $product->labels()->sync(array_unique($labelIds));
                }
                if (! empty($data['collections'])) {
                    $product->collections()->sync($data['collections']);
                }

                return $product;
            });
        } catch (Throwable $exception) {
            MediaLibrary::deleteIfUnused($storedPaths);
            throw $exception;
        }

        return response()->json([
            'message' => "{$product->name} added successfully.",
            'product' => $product->load(['category', 'brand', 'size', 'images', 'labels', 'collections', 'variants.attribute', 'skuVariants.options.attribute']),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        if (! $request->has('discount_type')) {
            $request->merge(['discount_type' => $product->discount_type ?: 'percent']);
        }
        $data = $this->validated($request);

        $updates = [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'brand_id'    => $data['brand_id'] ?? null,
            'size_id'     => $data['size_id'] ?? null,
            'price'       => $data['price'],
            'discount'    => $data['discount'] ?? $product->discount,
            'discount_type' => $data['discount_type'] ?? $product->discount_type,
            'emoji'       => $data['emoji'] ?? $product->emoji,
            'is_active'   => $request->boolean('is_active'),
        ];
        if ($product->skuVariants()->exists()) {
            $updates['stock'] = $product->skuVariants()->sum('stock');
        } else {
            $updates['stock'] = $data['stock'];
        }
        if ($request->has('sale_price')) {
            $updates['sale_price'] = $data['sale_price'] ?? null;
            $updates['sale_starts_at'] = isset($data['sale_starts_at']) ? \Illuminate\Support\Carbon::parse($data['sale_starts_at']) : null;
            $updates['sale_ends_at'] = isset($data['sale_ends_at']) ? \Illuminate\Support\Carbon::parse($data['sale_ends_at']) : null;
        }
        if ($request->hasAny(['sale_discount', 'sale_discount_type', 'sale_starts_at', 'sale_ends_at'])) {
            $updates['sale_discount'] = $data['sale_discount'] ?? null;
            $updates['sale_discount_type'] = $data['sale_discount_type'] ?? 'percent';
            $updates['sale_starts_at'] = isset($data['sale_starts_at']) ? \Illuminate\Support\Carbon::parse($data['sale_starts_at']) : null;
            $updates['sale_ends_at'] = isset($data['sale_ends_at']) ? \Illuminate\Support\Carbon::parse($data['sale_ends_at']) : null;
        }
        if ($request->has('shipping_weight')) {
            $updates['shipping_weight'] = $data['shipping_weight'] ?? null;
            $updates['weight_unit'] = $data['weight_unit'] ?? 'g';
            $updates['shipping_length'] = $data['shipping_length'] ?? null;
            $updates['shipping_width'] = $data['shipping_width'] ?? null;
            $updates['shipping_height'] = $data['shipping_height'] ?? null;
            $updates['dimension_unit'] = $data['dimension_unit'] ?? 'cm';
        }
        $product->update($updates);

        if ($request->has('labels')) {
            $product->labels()->sync($data['labels'] ?? []);
        }
        $product->load(['category', 'size', 'brand', 'images', 'labels', 'skuVariants.options.attribute']);

        return response()->json(['message' => "{$product->name} updated.", 'product' => $product]);
    }

    public function updateCover(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'cover_image_id' => ['required_without_all:cover_image,cover_image_path', 'nullable', 'integer', Rule::exists('product_images', 'id')->where('product_id', $product->id)],
            'cover_image_path' => ['required_without_all:cover_image_id,cover_image', 'nullable', 'string', 'max:500', 'regex:/^(?!\/)(?!.*(?:^|\/)\.\.(?:\/|$))[A-Za-z0-9._\/-]+$/'],
            'cover_image' => ['required_without_all:cover_image_id,cover_image_path', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        if (! empty($data['cover_image_path']) && ! Storage::disk('public')->exists($data['cover_image_path'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cover_image_path' => ['The selected media image is no longer available. Refresh the library and choose another image.'],
            ]);
        }
        $newPath = null;
        $uploadedPath = false;
        try {
            DB::transaction(function () use ($product, $data, $request, &$newPath, &$uploadedPath): void {
                $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
                if ($request->hasFile('cover_image') || ! empty($data['cover_image_path'])) {
                    if ($product->images()->count() >= 6) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'cover_image' => ['A product can have no more than six photos. Remove a photo before adding another.'],
                        ]);
                    }
                    if ($request->hasFile('cover_image')) {
                        $newPath = $request->file('cover_image')->store('products', 'public');
                        $uploadedPath = true;
                    } else {
                        $newPath = $data['cover_image_path'];
                    }
                    if (! $newPath) {
                        throw new \RuntimeException('The storefront image could not be saved. Please try again.');
                    }
                    $product->images()->update(['is_cover' => false]);
                    $product->images()->create([
                        'path' => $newPath,
                        'sort_order' => (int) $product->images()->max('sort_order') + 1,
                        'is_cover' => true,
                        'is_legacy_cover' => false,
                    ]);
                } else {
                    $product->images()->update(['is_cover' => false]);
                    $product->images()->whereKey($data['cover_image_id'])->update(['is_cover' => true]);
                }
            });
        } catch (Throwable $exception) {
            if ($uploadedPath && $newPath) {
                MediaLibrary::deleteIfUnused($newPath);
            }
            throw $exception;
        }

        $product->load('images');
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Storefront product photo updated.', 'product' => $product]);
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Storefront product photo updated.');
    }

    public function updateImages(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'gallery_uploads' => ['nullable', 'array', 'max:6'],
            'gallery_uploads.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_paths' => ['nullable', 'array', 'max:6'],
            'image_paths.*' => ['required', 'string', 'distinct', ...MediaLibrary::imagePathRules()],
            'remove_image_ids' => ['nullable', 'array', 'max:6'],
            'remove_image_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_images', 'id')->where('product_id', $product->id),
            ],
            'cover_image_id' => [
                'nullable',
                'integer',
                Rule::exists('product_images', 'id')->where('product_id', $product->id),
            ],
            'cover_new_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $mediaPaths = array_values($data['image_paths'] ?? []);
        foreach ($mediaPaths as $path) {
            if (! Storage::disk('public')->exists($path)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'image_paths' => ['A selected Media library image is no longer available. Refresh and choose it again.'],
                ]);
            }
        }

        $removeIds = array_map('intval', $data['remove_image_ids'] ?? []);
        $existingImages = $product->images()->get();
        $retainedImages = $existingImages->reject(fn ($image) => in_array($image->id, $removeIds, true));
        $uploads = $request->file('gallery_uploads', []);
        $newImageCount = count($uploads) + count($mediaPaths);
        if ($retainedImages->count() + $newImageCount > 6) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'gallery_uploads' => ['A product can have no more than six photos. Remove a photo before adding another.'],
            ]);
        }

        if (array_intersect($retainedImages->pluck('path')->all(), $mediaPaths) !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image_paths' => ['Each product photo can only be added once.'],
            ]);
        }

        $coverImageId = isset($data['cover_image_id']) ? (int) $data['cover_image_id'] : null;
        if ($coverImageId !== null && in_array($coverImageId, $removeIds, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cover_image_id' => ['Choose a photo that is not marked for removal as the storefront photo.'],
            ]);
        }
        $coverNewIndex = isset($data['cover_new_image_index']) ? (int) $data['cover_new_image_index'] : null;
        if ($coverNewIndex !== null && $coverNewIndex >= $newImageCount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cover_new_image_index' => ['Choose one of the newly added photos as the storefront photo.'],
            ]);
        }

        $storedPaths = [];
        try {
            foreach ($uploads as $file) {
                $path = $file->store('products', 'public');
                if (! $path) {
                    throw new \RuntimeException('A product photo could not be saved. Please try again.');
                }
                $storedPaths[] = $path;
            }

            $newPaths = array_merge($storedPaths, $mediaPaths);
            DB::transaction(function () use ($product, $removeIds, $newPaths, $coverImageId, $coverNewIndex): void {
                $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
                $removableIds = $product->images()->whereIn('id', $removeIds)->pluck('id')->all();
                if ($product->images()->count() - count($removableIds) + count($newPaths) > 6) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'gallery_uploads' => ['A product can have no more than six photos. Remove a photo before adding another.'],
                    ]);
                }
                if ($removableIds !== []) {
                    $product->images()->whereIn('id', $removableIds)->delete();
                }

                $sortOrder = (int) $product->images()->max('sort_order') + 1;
                $addedImages = collect($newPaths)->map(fn (string $path, int $index) => $product->images()->create([
                    'path' => $path,
                    'sort_order' => $sortOrder + $index,
                    'is_cover' => false,
                    'is_legacy_cover' => false,
                ]));

                $cover = $coverImageId !== null
                    ? $product->images()->whereKey($coverImageId)->first()
                    : ($coverNewIndex !== null ? $addedImages->get($coverNewIndex) : null);
                if ($coverImageId !== null && ! $cover) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cover_image_id' => ['The selected storefront photo is no longer available. Refresh and try again.'],
                    ]);
                }
                if (! $cover) {
                    $cover = $product->images()->where('is_cover', true)->first();
                }
                if (! $cover) {
                    $cover = $product->images()->orderBy('sort_order')->first();
                }

                $product->images()->update(['is_cover' => false]);
                if ($cover) {
                    $product->images()->whereKey($cover->id)->update(['is_cover' => true]);
                }
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                MediaLibrary::deleteIfUnused($storedPaths);
            }
            throw $exception;
        }

        $product->load('images');

        return response()->json([
            'message' => 'Product gallery updated.',
            'product' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $name = $product->name;

        // 🧹 delete photo files from disk too
        $product->delete();

        return response()->json(['message' => "{$name} deleted."]);
    }

    public function submissions(Request $request)
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ]);
        $search = trim($data['search'] ?? '');
        $status = $data['status'] ?? '';
        $products = Product::whereNotNull('submitted_by')
            ->when($status !== '', fn ($query) => $query->where('review_status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('submitter', fn ($submitter) => $submitter->where('name', 'like', '%' . $search . '%'));
            }))
            ->with(['submitter', 'category', 'size', 'images'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.product-submissions', compact('products'));
    }

    public function reviewSubmission(Product $product)
    {
        abort_unless($product->submitted_by, 404);
        $product->load(['submitter', 'category', 'size', 'images']);

        return view('admin.pages.product-submission-review', compact('product'));
    }

    public function approveSubmission(Product $product)
    {
        abort_unless($product->submitted_by && $product->review_status === 'pending', 404);

        $product->update([
            'review_status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'is_active' => true,
        ]);

        $message = "{$product->name} approved and published.";

        return request()->expectsJson()
            ? response()->json(['message' => $message])
            : redirect('/admin/product-submissions')->with('status', $message);
    }

    public function rejectSubmission(Product $product)
    {
        abort_unless($product->submitted_by && $product->review_status === 'pending', 404);

        $product->update([
            'review_status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'is_active' => false,
        ]);

        $message = "{$product->name} rejected.";

        return request()->expectsJson()
            ? response()->json(['message' => $message])
            : redirect('/admin/product-submissions')->with('status', $message);
    }
}