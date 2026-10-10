<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\SpecificationAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductSpecificationController extends Controller
{
    private const ATTRIBUTE_TYPES = ['text', 'number', 'select', 'boolean', 'size'];

    public function groups(Request $request)
    {
        $query = Category::query()->withCount(['products', 'specificationAttributes'])->orderBy('name');
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (in_array($request->query('status'), ['active', 'inactive'], true)) {
            $query->where('is_active', $request->query('status') === 'active');
        }

        return view('admin.pages.product-specification.groups', [
            'groups' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $request->query('status', ''),
        ]);
    }

    public function showGroup(Request $request, Category $category)
    {
        $products = $category->products()
            ->with(['images', 'size'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $attributes = $category->specificationAttributes()->get();

        return view('admin.pages.product-specification.group-show', compact('category', 'products', 'attributes'));
    }

    public function editProduct(Product $product)
    {
        $product->load(['category', 'specificationValues']);
        $attributes = $product->category?->specificationAttributes ?? collect();

        abort_unless($product->category, 404);

        return view('admin.pages.product-specification.product-edit', [
            'product' => $product,
            'attributes' => $attributes,
            'sizes' => Size::where('is_active', true)->orderBy('ml')->get(),
            'values' => $product->specificationValues->pluck('value', 'specification_attribute_id'),
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        abort_unless($product->category, 404);
        $attributes = $product->category->specificationAttributes()->get();
        $input = $request->input('specifications', []);
        if (! is_array($input)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'specifications' => ['Specification values must be submitted as fields.'],
            ]);
        }

        $allowedIds = $attributes->modelKeys();
        foreach (array_keys($input) as $attributeId) {
            if (! ctype_digit((string) $attributeId) || ! in_array((int) $attributeId, $allowedIds, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'specifications' => ['One or more fields do not belong to this product group. Refresh and try again.'],
                ]);
            }
        }

        $sizes = Size::where('is_active', true)->orderBy('ml')->get();
        $sizeValues = $sizes->map(fn (Size $size) => $size->name.' ('.$size->ml.' ml)')->all();
        $values = [];
        $errors = [];

        foreach ($attributes as $attribute) {
            $rawValue = $input[$attribute->id] ?? '';
            if (! is_scalar($rawValue)) {
                $errors['specifications.'.$attribute->id] = ['Choose a valid value for '.$attribute->name.'.'];
                continue;
            }
            $value = trim((string) $rawValue);
            if ($value === '') {
                if ($attribute->is_required) {
                    $errors['specifications.'.$attribute->id] = [$attribute->name.' is required.'];
                }
                continue;
            }

            if (mb_strlen($value) > 1000) {
                $errors['specifications.'.$attribute->id] = [$attribute->name.' must be 1000 characters or fewer.'];
                continue;
            }

            $valid = match ($attribute->type) {
                'number' => is_numeric($value),
                'select' => in_array($value, $attribute->options ?? [], true),
                'boolean' => in_array($value, ['Yes', 'No'], true),
                'size' => in_array($value, $sizeValues, true),
                default => true,
            };
            if (! $valid) {
                $errors['specifications.'.$attribute->id] = ['Choose a valid value for '.$attribute->name.'.'];
                continue;
            }
            $values[$attribute->id] = $value;
        }

        if ($errors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($product, $attributes, $values): void {
            $product->specificationValues()
                ->whereIn('specification_attribute_id', $attributes->modelKeys())
                ->delete();

            foreach ($values as $attributeId => $value) {
                $product->specificationValues()->create([
                    'specification_attribute_id' => $attributeId,
                    'value' => $value,
                ]);
            }
        });

        return redirect()->route('admin.product-specification.groups.show', $product->category_id)
            ->with('success', 'Product specifications saved. They are now visible on its storefront detail page.');
    }

    public function editGroup(Category $category)
    {
        return view('admin.pages.product-specification.group-edit', [
            'category' => $category,
            'categoryIconChoices' => config('category_icons.choices'),
        ]);
    }

    public function updateGroup(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['required', 'string', Rule::in(collect(config('category_icons.choices'))->pluck('value')->all())],
            'is_active' => ['required', 'boolean'],
        ]);

        $baseSlug = Str::slug($data['name']) ?: 'product-group';
        $slug = $baseSlug;
        $suffix = 2;
        while (Category::where('slug', $slug)->whereKeyNot($category->id)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }
        $category->update($data + ['slug' => $slug]);

        return redirect()->route('admin.product-specification.groups')->with('success', 'Product group updated.');
    }

    public function destroyGroup(Category $category)
    {
        if ($category->products()->exists()
            || DB::table('announcement_category')->where('category_id', $category->id)->exists()) {
            return redirect()->route('admin.product-specification.groups')
                ->with('error', 'This group is still in use by products or announcements and cannot be deleted.');
        }

        $category->delete();

        return redirect()->route('admin.product-specification.groups')->with('success', 'Empty product group deleted.');
    }

    public function attributes(Request $request)
    {
        $query = SpecificationAttribute::query()->with('category')->orderBy('category_id')->orderBy('sort_order')->orderBy('name');
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if (is_numeric($categoryId)) {
            $query->where('category_id', (int) $categoryId);
        } else {
            $categoryId = '';
        }

        return view('admin.pages.product-specification.attributes', [
            'attributes' => $query->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'sizes' => Size::orderBy('ml')->get(),
            'search' => $search,
            'categoryId' => $categoryId,
            'types' => self::ATTRIBUTE_TYPES,
        ]);
    }

    public function storeAttribute(Request $request)
    {
        $data = $this->validatedAttribute($request);
        SpecificationAttribute::create($data);

        return redirect()->route('admin.product-specification.attributes', ['category_id' => $data['category_id']])
            ->with('success', 'Specification attribute created.');
    }

    public function editAttribute(SpecificationAttribute $specificationAttribute)
    {
        return view('admin.pages.product-specification.attribute-edit', [
            'attribute' => $specificationAttribute->load('category'),
            'categories' => Category::orderBy('name')->get(),
            'types' => self::ATTRIBUTE_TYPES,
        ]);
    }

    public function updateAttribute(Request $request, SpecificationAttribute $specificationAttribute)
    {
        $data = $this->validatedAttribute($request, $specificationAttribute);
        $specificationAttribute->update($data);

        return redirect()->route('admin.product-specification.attributes', ['category_id' => $data['category_id']])
            ->with('success', 'Specification attribute updated.');
    }

    public function destroyAttribute(SpecificationAttribute $specificationAttribute)
    {
        $categoryId = $specificationAttribute->category_id;
        $specificationAttribute->delete();

        return redirect()->route('admin.product-specification.attributes', ['category_id' => $categoryId])
            ->with('success', 'Specification attribute deleted.');
    }

    private function validatedAttribute(Request $request, ?SpecificationAttribute $attribute = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('specification_attributes', 'name')
                    ->where('category_id', $request->input('category_id'))
                    ->ignore($attribute?->id),
            ],
            'type' => ['required', Rule::in(self::ATTRIBUTE_TYPES)],
            'options' => ['nullable', 'string', 'max:2000'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
        $data['is_required'] = $request->boolean('is_required');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $options = collect(preg_split('/\r\n|\r|\n/', (string) ($data['options'] ?? '')))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->unique()
            ->values();
        if ($data['type'] === 'select' && $options->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'options' => ['Add at least one choice for a select field.'],
            ]);
        }
        $data['options'] = $data['type'] === 'select' ? $options->all() : null;

        return $data;
    }
}
