<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSkuVariant;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use App\Models\ProductLabel;
use App\Models\ProductVariant;
use App\Support\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Throwable;

class CatalogManagementController extends Controller
{
    public function prices()
    {
        return view('admin.pages.ecommerce.prices', [
            'products' => Product::with(['category', 'images'])->orderBy('name')->get(),
        ]);
    }

    public function updatePrices(Request $request)
    {
        $data = $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'products.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'products.*.sale_starts_at' => ['nullable', 'date'],
            'products.*.sale_ends_at' => ['nullable', 'date'],
        ]);
        $prices = $data['products'];
        $ids = array_map('intval', array_keys($prices));

        abort_unless(Product::whereKey($ids)->count() === count($ids), 422);
        foreach ($prices as $id => $values) {
            if (($values['sale_price'] ?? null) === null) {
                continue;
            }
            if ((float) $values['sale_price'] >= (float) $values['price']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "products.{$id}.sale_price" => ['Sale price must be lower than the regular price.'],
                ]);
            }
            if (empty($values['sale_starts_at']) || empty($values['sale_ends_at']) || $values['sale_ends_at'] <= $values['sale_starts_at']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "products.{$id}.sale_ends_at" => ['A sale requires a start and end time, and the end must be later.'],
                ]);
            }
        }

        DB::transaction(function () use ($prices, $ids): void {
            $products = Product::whereKey($ids)->lockForUpdate()->get()->keyBy('id');
            foreach ($prices as $id => $values) {
                $products->get((int) $id)->update($values);
            }
        });

        return redirect()->route('admin.ecommerce.prices')->with('success', 'Product prices and scheduled sale periods updated.');
    }

    public function startRandomSales()
    {
        $now = now();
        $updated = 0;

        DB::transaction(function () use ($now, &$updated): void {
            Product::query()
                ->where('is_active', true)
                ->where('review_status', 'approved')
                ->where('price', '>', 0)
                ->where(function ($query) use ($now): void {
                    $query->whereNull('sale_price')
                        ->orWhere(function ($expired) use ($now): void {
                            $expired->whereNotNull('sale_ends_at')
                                ->where('sale_ends_at', '<=', $now);
                        });
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->chunkById(100, function ($products) use ($now, &$updated): void {
                    foreach ($products as $product) {
                        $discountPercent = random_int(5, 30);
                        $saleEndsAt = Carbon::instance($now)->addDays(random_int(1, 7));
                        $product->update([
                            'sale_price' => round((float) $product->price * (1 - $discountPercent / 100), 2),
                            'sale_starts_at' => $now,
                            'sale_ends_at' => $saleEndsAt,
                        ]);
                        $updated++;
                    }
                });
        });

        if ($updated === 0) {
            return redirect()->route('admin.ecommerce.prices')
                ->with('error', 'No published products are eligible. Existing active or scheduled sales were left unchanged.');
        }

        return redirect()->route('admin.ecommerce.prices')
            ->with('success', "Random 5–30% timed sales started for {$updated} published product(s).");
    }

    public function inventory()
    {
        return view('admin.pages.ecommerce.inventory', [
            'products' => Product::with(['category', 'images', 'skuVariants.options.attribute'])->orderBy('name')->get(),
        ]);
    }

    public function updateInventory(Request $request)
    {
        $data = $request->validate([
            'products' => ['nullable', 'array'],
            'products.*.stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'sku_variants' => ['nullable', 'array'],
            'sku_variants.*.stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);
        $productStock = $data['products'] ?? [];
        $variantStock = $data['sku_variants'] ?? [];
        if ($productStock === [] && $variantStock === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'products' => ['Enter at least one stock quantity before saving.'],
            ]);
        }

        $directProductIds = array_map('intval', array_keys($productStock));
        $variantIds = array_map('intval', array_keys($variantStock));
        $variantProductIds = $variantIds
            ? ProductSkuVariant::whereKey($variantIds)->pluck('product_id')->map(fn ($id) => (int) $id)->all()
            : [];
        abort_unless(count($variantProductIds) === count($variantIds), 422);
        $productIds = array_values(array_unique(array_merge($directProductIds, $variantProductIds)));
        abort_unless(Product::whereKey($productIds)->count() === count($productIds), 422);

        DB::transaction(function () use ($productStock, $variantStock, $productIds, $variantIds): void {
            $products = Product::whereKey($productIds)->lockForUpdate()->get()->keyBy('id');
            $variants = ProductSkuVariant::whereKey($variantIds)->lockForUpdate()->get()->keyBy('id');
            foreach ($productStock as $id => $values) {
                $product = $products->get((int) $id);
                if ($product->skuVariants()->exists()) {
                    abort(422, 'Update stock on each sellable option instead of the aggregate product quantity.');
                }
                $product->update($values);
            }
            foreach ($variantStock as $id => $values) {
                $variant = $variants->get((int) $id);
                if (! $variant || ! $products->has($variant->product_id)) {
                    abort(422);
                }
                $variant->update($values);
            }
            foreach (array_unique($variants->pluck('product_id')->all()) as $productId) {
                $products->get((int) $productId)->update([
                    'stock' => ProductSkuVariant::where('product_id', $productId)->sum('stock'),
                ]);
            }
        });

        return redirect()->route('admin.ecommerce.inventory')->with('success', 'Inventory quantities updated.');
    }

    public function categories()
    {
        return view('admin.pages.ecommerce.categories', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function categoryProducts(Category $category)
    {
        $products = $category->products()->with(['images', 'category'])->orderBy('name')->paginate(24);

        return view('admin.pages.ecommerce.category-products', compact('category', 'products'));
    }

    public function attributes()
    {
        return view('admin.pages.ecommerce.attributes', [
            'attributes' => ProductAttribute::with('variants')->orderBy('name')->get(),
            'products' => Product::with(['variants.attribute', 'images', 'category'])->orderBy('name')->get(),
        ]);
    }

    public function storeAttribute(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:product_attributes,name'],
            'type' => ['required', Rule::in(['color', 'text', 'size'])],
        ]);

        ProductAttribute::create($data);

        return redirect()->route('admin.ecommerce.attributes')->with('success', 'Product attribute created.');
    }

    public function updateAttribute(Request $request, ProductAttribute $attribute)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('product_attributes', 'name')->ignore($attribute->id)],
            'type' => ['required', Rule::in(['color', 'text', 'size'])],
        ]);

        $attribute->update($data);

        return redirect()->route('admin.ecommerce.attributes')->with('success', 'Product attribute updated.');
    }

    public function destroyAttribute(ProductAttribute $attribute)
    {
        $imagePaths = $attribute->variants()->whereNotNull('image_path')->pluck('image_path')->unique()->values()->all();

        DB::transaction(function () use ($attribute): void {
            $attribute->variants()->delete();
            $attribute->delete();
        });

        if ($imagePaths !== []) {
            MediaLibrary::deleteIfUnused($imagePaths);
        }

        return redirect()->route('admin.ecommerce.attributes')->with('success', 'Product attribute deleted.');
    }

    public function storeVariants(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'options' => ['nullable', 'array', 'max:100'],
            'options.*.id' => ['nullable', 'integer', 'distinct'],
            'options.*.value' => ['required', 'string', 'max:120'],
            'options.*.description' => ['nullable', 'string', 'max:500'],
            'options.*.color_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'options.*.image' => ['nullable', 'image', 'max:3072'],
            'options.*.image_path' => MediaLibrary::imagePathRules(),
            'options.*.remove_image' => ['nullable', 'boolean'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $attribute = ProductAttribute::findOrFail($data['product_attribute_id']);
        $submitted = collect($data['options'] ?? []);
        $existing = ProductVariant::where('product_id', $product->id)
            ->where('product_attribute_id', $attribute->id)
            ->get()
            ->keyBy('id');

        foreach ($submitted as $option) {
            if (! empty($option['id'])) {
                abort_unless($existing->has((int) $option['id']), 422);
            }
        }
        $submittedIds = $submitted->pluck('id')->filter()->map(fn ($id) => (int) $id);
        $removed = $existing->except($submittedIds->all());
        foreach ($removed as $option) {
            if (DB::table('product_sku_variant_options')->where('product_variant_id', $option->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'options' => ['This option is used by sellable stock combinations. Remove or update those combinations before deleting it.'],
                ]);
            }
        }

        $values = $submitted->pluck('value')->map(fn ($value) => mb_strtolower(trim($value)));
        if ($values->duplicates()->isNotEmpty()) {
            return back()->withErrors(['options' => 'Option values must be unique for this product attribute.'])->withInput();
        }

        $storedPaths = [];
        $oldPaths = $existing->pluck('image_path')->filter()->all();

        try {
            DB::transaction(function () use ($submitted, $existing, $removed, $product, $attribute, &$storedPaths): void {
                foreach ($submitted as $option) {
                    $imagePath = null;
                    $previous = null;
                    if (! empty($option['id'])) {
                        $previous = $existing->get((int) $option['id']);
                        if (empty($option['remove_image'])) {
                            $imagePath = $previous->image_path;
                        }
                    }
                    if (! empty($option['image_path'])) {
                        $imagePath = $option['image_path'];
                    }
                    if (isset($option['image'])) {
                        $imagePath = $option['image']->store('product-variants', 'public');
                        if (! $imagePath) {
                            throw new \RuntimeException('A product option image could not be saved. Please try again.');
                        }
                        $storedPaths[] = $imagePath;
                    }

                    $values = [
                        'value' => trim($option['value']),
                        'description' => $option['description'] ?? null,
                        'color_hex' => $option['color_hex'] ?? null,
                        'image_path' => $imagePath,
                    ];
                    if ($previous) {
                        $previous->update($values);
                    } else {
                        ProductVariant::create($values + [
                            'product_id' => $product->id,
                            'product_attribute_id' => $attribute->id,
                        ]);
                    }
                }
                $removed->each->delete();
            });
        } catch (Throwable $exception) {
            MediaLibrary::deleteIfUnused($storedPaths);
            throw $exception;
        }

        $retainedPaths = ProductVariant::where('product_id', $product->id)
            ->where('product_attribute_id', $attribute->id)
            ->pluck('image_path')->filter()->all();
        MediaLibrary::deleteIfUnused(array_values(array_diff($oldPaths, $retainedPaths)));

        return redirect()->route('admin.ecommerce.attributes', [
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
        ])->with('success', 'Product options updated.');
    }

    public function collections()
    {
        return view('admin.pages.ecommerce.collections', [
            'collections' => ProductCollection::withCount('products')->with('products.images')->latest()->get(),
        ]);
    }

    public function createCollection()
    {
        return view('admin.pages.ecommerce.collections-create', [
            'products' => Product::with(['images', 'category'])->orderBy('name')->get(),
        ]);
    }

    public function storeCollection(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at', 'after:now'],
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ]);

        DB::transaction(function () use ($data): void {
            $collection = ProductCollection::create(collect($data)->except('products')->all());
            $collection->products()->sync($data['products']);
        });

        return redirect()->route('admin.ecommerce.collections')->with('success', 'Scheduled collection created.');
    }

    public function destroyCollection(ProductCollection $collection)
    {
        DB::transaction(fn () => $collection->delete());

        return redirect()->route('admin.ecommerce.collections')->with('success', 'Collection deleted.');
    }

    public function labels()
    {
        return view('admin.pages.ecommerce.labels', [
            'labels' => ProductLabel::withCount('products')->with('products.images')->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function createLabel()
    {
        return view('admin.pages.ecommerce.labels-create', [
            'products' => Product::with(['images', 'category'])->orderBy('name')->get(),
        ]);
    }

    public function storeLabel(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(ProductLabel::TYPES)],
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ]);

        DB::transaction(function () use ($data): void {
            $label = ProductLabel::firstOrCreate(
                ['name' => trim($data['name']), 'type' => $data['type']],
                []
            );
            $label->products()->sync($data['products']);
        });

        return redirect()->route('admin.ecommerce.labels')->with('success', 'Product label set saved.');
    }

    public function destroyLabel(ProductLabel $label)
    {
        DB::transaction(fn () => $label->delete());

        return redirect()->route('admin.ecommerce.labels')->with('success', 'Product label set removed.');
    }
}
