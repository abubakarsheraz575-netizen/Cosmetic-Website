<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Support\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
            'sort' => ['nullable', Rule::in(['name', 'products_count', 'is_active'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sortColumns = [
            'name' => 'brands.name',
            'products_count' => 'products_count',
            'is_active' => 'brands.is_active',
        ];
        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        $search = trim($filters['q'] ?? '');

        $brands = Brand::withCount('products')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('brands.name', 'like', '%' . $search . '%')
                    ->orWhere('brands.slug', 'like', '%' . $search . '%')
                    ->orWhere('brands.description', 'like', '%' . $search . '%');
            }))
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('brands.id')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return view('admin.pages.ecommerce.brands.index', compact('brands', 'filters', 'sort', 'direction'));
    }

    public function show(Request $request, Brand $brand)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
            'sort' => ['nullable', Rule::in(['name', 'price', 'stock', 'is_active'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sortColumns = [
            'name' => 'products.name',
            'price' => 'products.price',
            'stock' => 'products.stock',
            'is_active' => 'products.is_active',
        ];
        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        $search = trim($filters['q'] ?? '');

        $products = $brand->products()
            ->with('images')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('products.name', 'like', '%' . $search . '%')
                    ->orWhere('products.slug', 'like', '%' . $search . '%')
                    ->orWhere('products.description', 'like', '%' . $search . '%');
            }))
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('products.id')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return view('admin.pages.ecommerce.brands.show', compact('brand', 'products', 'filters', 'sort', 'direction'));
    }

    public function create()
    {
        return $this->form(null);
    }

    public function edit(Brand $brand)
    {
        $brand->load('products');

        return $this->form($brand);
    }

    private function form(?Brand $brand)
    {
        $products = Product::with('images')->orderBy('name')->get();

        return view('admin.pages.ecommerce.brands.form', compact('brand', 'products'));
    }

    private function validated(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('brands', 'name')->ignore($brand?->id)],
            'description' => ['nullable', 'string', 'max:3000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_path' => MediaLibrary::imagePathRules(),
            'remove_logo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'products' => ['nullable', 'array', 'max:500'],
            'products.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $uploadedLogoPath = $request->file('logo')?->store('brands', 'public');
        if ($request->hasFile('logo') && ! $uploadedLogoPath) {
            throw new \RuntimeException('The brand image could not be saved. Please try again.');
        }
        $logoPath = $uploadedLogoPath ?: ($data['logo_path'] ?? null);

        try {
            $brand = DB::transaction(function () use ($data, $logoPath) {
                $brand = Brand::create([
                    'name' => $data['name'],
                    'slug' => $this->uniqueSlug($data['name']),
                    'logo_path' => $logoPath,
                    'description' => $data['description'] ?? null,
                    'website_url' => $data['website_url'] ?? null,
                    'is_active' => $data['is_active'] ?? false,
                ]);

                $this->assignProducts($brand, $data['products'] ?? []);

                return $brand;
            });
        } catch (\Throwable $exception) {
            if ($uploadedLogoPath) {
                MediaLibrary::deleteIfUnused($uploadedLogoPath);
            }

            throw $exception;
        }

        return redirect()->route('admin.ecommerce.brands')
            ->with('success', "{$brand->name} was created.");
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $this->validated($request, $brand);
        $uploadedLogoPath = $request->file('logo')?->store('brands', 'public');
        if ($request->hasFile('logo') && ! $uploadedLogoPath) {
            throw new \RuntimeException('The brand image could not be saved. Please try again.');
        }
        $newLogoPath = $uploadedLogoPath ?: ($data['logo_path'] ?? null);
        $oldLogoPath = $brand->logo_path;
        $removeLogo = !$newLogoPath && $request->boolean('remove_logo');

        try {
            DB::transaction(function () use ($brand, $data, $newLogoPath, $removeLogo) {
                $brand->update([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'website_url' => $data['website_url'] ?? null,
                    'is_active' => $data['is_active'] ?? false,
                    ...($newLogoPath ? ['logo_path' => $newLogoPath] : []),
                    ...($removeLogo ? ['logo_path' => null] : []),
                ]);

                $this->assignProducts($brand, $data['products'] ?? []);
            });
        } catch (\Throwable $exception) {
            if ($uploadedLogoPath) {
                MediaLibrary::deleteIfUnused($uploadedLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath || $removeLogo) {
            if ($oldLogoPath) {
                MediaLibrary::deleteIfUnused($oldLogoPath);
            }
        }

        return redirect()->route('admin.ecommerce.brands')
            ->with('success', "{$brand->name} was updated.");
    }

    public function destroy(Brand $brand)
    {
        $name = $brand->name;
        $logoPath = $brand->logo_path;
        $brand->delete();

        if ($logoPath) {
            MediaLibrary::deleteIfUnused($logoPath);
        }

        return redirect()->route('admin.ecommerce.brands')
            ->with('success', "{$name} was deleted.");
    }

    private function assignProducts(Brand $brand, array $productIds): void
    {
        $brand->products()->whereNotIn('id', $productIds)->update(['brand_id' => null]);

        if ($productIds !== []) {
            Product::whereIn('id', $productIds)->update(['brand_id' => $brand->id]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'brand';
        $slug = $base;
        $suffix = 2;

        while (Brand::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
