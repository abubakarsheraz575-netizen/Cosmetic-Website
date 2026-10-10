<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecificationValue;
use App\Models\Size;
use App\Support\AdminEmailNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductSubmissionController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->with(['specificationAttributes' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->orderBy('name')
            ->get();
        $sizes = Size::where('is_active', true)->orderBy('ml')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('user.pages.product-submissions', compact('categories', 'sizes', 'brands'));
    }

    public function submissions(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'per_page' => ['nullable', Rule::in(['10', '20', '50'])],
        ]);

        $submissions = Product::query()
            ->where('submitted_by', $request->user()->id)
            ->with(['images', 'category', 'size'])
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%' . $search . '%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('review_status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();

        return view('user.pages.my-submissions', compact('submissions', 'filters'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')->where('is_active', true)],
            'size_id' => [
                'nullable',
                Rule::exists('sizes', 'id')->where('is_active', true),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'emoji' => ['nullable', 'string', 'max:8'],
            'description' => ['nullable', 'string', 'max:1000'],
            'shipping_weight' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'weight_unit' => ['nullable', Rule::in(['mg', 'g', 'kg', 'lb', 'oz'])],
            'shipping_length' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'shipping_width' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'shipping_height' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'dimension_unit' => ['nullable', Rule::in(['mm', 'cm', 'm', 'in'])],
            'specifications' => ['nullable', 'array', 'max:100'],
            'images' => ['required', 'array', 'min:1', 'max:6'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image_index' => ['required', 'integer', 'min:0'],
        ]);
        if ((int) $data['cover_image_index'] >= count($request->file('images', []))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cover_image_index' => ['Choose one of the uploaded photos as the storefront photo.'],
            ]);
        }

        $category = Category::whereKey($data['category_id'])
            ->where('is_active', true)
            ->with('specificationAttributes')
            ->firstOrFail();
        $specificationInput = $data['specifications'] ?? [];
        $attributes = $category->specificationAttributes->keyBy('id');
        foreach (array_keys($specificationInput) as $attributeId) {
            if (! ctype_digit((string) $attributeId) || ! $attributes->has((int) $attributeId)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'specifications' => ['One or more specification fields do not belong to the selected category.'],
                ]);
            }
        }

        $sizes = Size::where('is_active', true)->orderBy('ml')->get();
        $validSizeOptions = $sizes->map(fn (Size $size) => $size->name.' ('.$size->ml.' ml)')->all();
        $specificationValues = [];
        $specificationErrors = [];
        foreach ($attributes as $attribute) {
            $rawValue = $specificationInput[$attribute->id] ?? '';
            if (! is_scalar($rawValue)) {
                $specificationErrors['specifications.'.$attribute->id] = ['Choose a valid value for '.$attribute->name.'.'];
                continue;
            }
            $value = trim((string) $rawValue);
            if ($value === '') {
                if ($attribute->is_required) {
                    $specificationErrors['specifications.'.$attribute->id] = [$attribute->name.' is required.'];
                }
                continue;
            }
            if (mb_strlen($value) > 1000) {
                $specificationErrors['specifications.'.$attribute->id] = [$attribute->name.' must be 1000 characters or fewer.'];
                continue;
            }
            $isValid = match ($attribute->type) {
                'number' => is_numeric($value),
                'select' => in_array($value, $attribute->options ?? [], true),
                'boolean' => in_array($value, ['Yes', 'No'], true),
                'size' => in_array($value, $validSizeOptions, true),
                default => true,
            };
            if (! $isValid) {
                $specificationErrors['specifications.'.$attribute->id] = ['Choose a valid value for '.$attribute->name.'.'];
                continue;
            }
            $specificationValues[$attribute->id] = $value;
        }
        if ($specificationErrors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($specificationErrors);
        }

        $storedPaths = [];
        try {
            $product = DB::transaction(function () use ($data, $request, $specificationValues, &$storedPaths) {
                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(6)),
                    'description' => $data['description'] ?? null,
                    'category_id' => $data['category_id'],
                    'brand_id' => $data['brand_id'] ?? null,
                    'size_id' => $data['size_id'] ?? null,
                    'price' => $data['price'],
                    'discount' => $data['discount'] ?? 0,
                    'stock' => $data['stock'],
                    'emoji' => $data['emoji'] ?? '🧴',
                    'shipping_weight' => $data['shipping_weight'] ?? null,
                    'weight_unit' => $data['weight_unit'] ?? 'g',
                    'shipping_length' => $data['shipping_length'] ?? null,
                    'shipping_width' => $data['shipping_width'] ?? null,
                    'shipping_height' => $data['shipping_height'] ?? null,
                    'dimension_unit' => $data['dimension_unit'] ?? 'cm',
                    'is_active' => false,
                    'submitted_by' => $request->user()->id,
                    'review_status' => 'pending',
                ]);

                foreach ($request->file('images') as $sortOrder => $image) {
                    $path = $image->store('products', 'public');
                    if (! $path) {
                        throw new \RuntimeException('A product image could not be stored. Please try again.');
                    }
                    $storedPaths[] = $path;
                    $product->images()->create([
                        'path' => $path,
                        'sort_order' => $sortOrder,
                        'is_cover' => $sortOrder === (int) $data['cover_image_index'],
                    ]);
                }

                foreach ($specificationValues as $attributeId => $value) {
                    ProductSpecificationValue::create([
                        'product_id' => $product->id,
                        'specification_attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }

                return $product;
            });
        } catch (\Throwable $exception) {
            if ($storedPaths) {
                Storage::disk('public')->delete($storedPaths);
            }

            throw $exception;
        }

        $adminNotified = AdminEmailNotifier::send('Product submitted for review', [
            'Product' => $product->name,
            'Submitted by' => $request->user()->name.' <'.$request->user()->email.'>',
            'Price' => '£'.number_format((float) $product->price, 2),
            'Stock' => (string) $product->stock,
            'Brand' => $product->brand?->name ?? 'Not specified',
            'Review queue' => route('admin.product-submissions.index'),
        ]);
        $message = 'Your product was submitted for admin review.'
            .($adminNotified ? '' : ' The admin email alert could not be delivered.');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'admin_notified' => $adminNotified,
                'submission' => [
                    'name' => $product->name,
                    'created_at' => $product->created_at->format('M j, Y'),
                    'review_status' => $product->review_status,
                ],
                'redirect' => route('user.product-submissions.submissions'),
            ], 201);
        }

        return redirect('/user/dashboard/products')
            ->with('status', $message);
    }
}
