<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\SpecificationAttribute;
use App\Models\User;

function specificationCategory(string $name = 'Face Care'): Category
{
    return Category::create([
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(),
        'icon' => '🧴',
        'description' => 'Specification test group',
        'is_active' => true,
    ]);
}

function specificationProduct(Category $category, array $overrides = []): Product
{
    return Product::create(array_merge([
        'name' => 'Test Serum '.uniqid(),
        'slug' => 'test-serum-'.uniqid(),
        'description' => 'A specification test product',
        'category_id' => $category->id,
        'price' => 24.50,
        'discount' => 0,
        'stock' => 14,
        'emoji' => '🧴',
        'is_active' => true,
    ], $overrides));
}

function specificationAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

test('product specification groups list category-backed groups with search and status filters', function () {
    $admin = specificationAdmin();
    $matched = specificationCategory('Hydration');
    $matched->update(['is_active' => false]);
    $other = specificationCategory('Cleansers');
    specificationProduct($matched);
    SpecificationAttribute::create(['category_id' => $matched->id, 'name' => 'Skin type', 'type' => 'select']);

    $this->actingAs($admin)
        ->get(route('admin.product-specification.groups', ['search' => 'Hydration', 'status' => 'inactive']))
        ->assertOk()
        ->assertSee('Hydration')
        ->assertSee('Inactive')
        ->assertSee('1')
        ->assertDontSee('Cleansers')
        ->assertViewHas('groups', fn ($groups) => $groups->first()->specification_attributes_count === 1);

    $this->get(route('admin.product-specification.groups'))
        ->assertOk()
        ->assertSee($other->name)
        ->assertSee('Product Specification');
});

test('a group opens its products with pictures inventory size tier and scoped attributes', function () {
    $admin = specificationAdmin();
    $category = specificationCategory();
    $size = Size::create(['name' => '50 ml', 'ml' => 50, 'is_active' => true]);
    $product = specificationProduct($category, ['size_id' => $size->id, 'stock' => 23]);
    ProductImage::create(['product_id' => $product->id, 'path' => 'products/serum-front.jpg', 'sort_order' => 0]);
    ProductImage::create(['product_id' => $product->id, 'path' => 'products/serum-back.jpg', 'sort_order' => 1]);
    SpecificationAttribute::create(['category_id' => $category->id, 'name' => 'Skin type', 'type' => 'select']);

    $this->actingAs($admin)
        ->get(route('admin.product-specification.groups.show', $category))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('serum-front.jpg')
        ->assertSee('serum-back.jpg')
        ->assertSee('23')
        ->assertSee('Small')
        ->assertSee('Skin type');
});

test('specification attributes are category scoped and independent from variant attributes', function () {
    $admin = specificationAdmin();
    $category = specificationCategory();
    $otherCategory = specificationCategory('Body Care');
    $variantAttribute = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);

    $this->actingAs($admin)->post(route('admin.product-specification.attributes.store'), [
        'category_id' => $category->id,
        'name' => 'Size',
        'type' => 'size',
        'is_required' => '1',
        'sort_order' => 2,
    ])->assertRedirect(route('admin.product-specification.attributes', ['category_id' => $category->id]));

    $sizeAttribute = SpecificationAttribute::where('category_id', $category->id)->where('name', 'Size')->firstOrFail();
    expect($sizeAttribute->type)->toBe('size')
        ->and($sizeAttribute->is_required)->toBeTrue()
        ->and($variantAttribute->fresh()->name)->toBe('Color');
    $this->actingAs($admin)->post(route('admin.product-specification.attributes.store'), [
        'category_id' => $category->id,
        'name' => 'Skin type',
        'type' => 'select',
        'options' => "Dry\nOily\nCombination",
    ])->assertRedirect(route('admin.product-specification.attributes', ['category_id' => $category->id]));
    expect(SpecificationAttribute::where('category_id', $category->id)->where('name', 'Skin type')->firstOrFail()->options)
        ->toBe(['Dry', 'Oily', 'Combination']);
    $this->get(route('admin.product-specification.attributes', ['category_id' => $category->id]))
        ->assertOk()
        ->assertSee('Size classifications')
        ->assertSee('Small')
        ->assertSee('10 ml');
    $this->get(route('admin.product-specification.attributes.edit', $sizeAttribute))
        ->assertOk()
        ->assertSee('Edit specification attribute');

    $this->actingAs($admin)->post(route('admin.product-specification.attributes.store'), [
        'category_id' => $category->id,
        'name' => 'Size',
        'type' => 'text',
    ])->assertSessionHasErrors('name');

    $this->post(route('admin.product-specification.attributes.store'), [
        'category_id' => $otherCategory->id,
        'name' => 'Size',
        'type' => 'number',
    ])->assertRedirect();
    expect(SpecificationAttribute::where('name', 'Size')->count())->toBe(2);

    $this->patch(route('admin.product-specification.attributes.update', $sizeAttribute), [
        'category_id' => $category->id,
        'name' => 'Volume',
        'type' => 'size',
        'is_required' => '0',
        'sort_order' => 3,
    ])->assertRedirect();
    expect($sizeAttribute->fresh()->name)->toBe('Volume');

    $this->delete(route('admin.product-specification.attributes.destroy', $sizeAttribute))->assertRedirect();
    expect(SpecificationAttribute::whereKey($sizeAttribute->id)->exists())->toBeFalse();
});

test('category specification values are saved per product and rendered on the storefront detail page', function () {
    $admin = specificationAdmin();
    $category = specificationCategory('Skin Care');
    $product = specificationProduct($category, [
        'slug' => 'specification-value-serum',
        'review_status' => 'approved',
    ]);
    $skinType = SpecificationAttribute::create([
        'category_id' => $category->id,
        'name' => 'Skin type',
        'type' => 'select',
        'options' => ['Dry', 'Oily', 'Combination'],
        'is_required' => true,
    ]);
    $vegan = SpecificationAttribute::create([
        'category_id' => $category->id,
        'name' => 'Vegan',
        'type' => 'boolean',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.product-specification.products.edit', $product))
        ->assertOk()
        ->assertSee('Skin type')
        ->assertSee('Dry')
        ->assertSee('Vegan');

    $this->patch(route('admin.product-specification.products.update', $product), [
        'specifications' => [
            $skinType->id => 'Combination',
            $vegan->id => 'Yes',
        ],
    ])->assertRedirect(route('admin.product-specification.groups.show', $category->id));

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('Skin type')
        ->assertSee('Combination')
        ->assertSee('Vegan')
        ->assertSee('Yes');

    $this->patch(route('admin.product-specification.products.update', $product), [
        'specifications' => [
            $skinType->id => 'Not an available choice',
            $vegan->id => 'Yes',
        ],
    ])->assertSessionHasErrors('specifications.'.$skinType->id);

    $this->patch(route('admin.product-specification.products.update', $product), [
        'specifications' => [$vegan->id => 'No'],
    ])->assertSessionHasErrors('specifications.'.$skinType->id);
});

test('groups can be edited but groups containing products cannot be deleted', function () {
    $admin = specificationAdmin();
    $category = specificationCategory();
    specificationProduct($category);

    $this->actingAs($admin)->patch(route('admin.product-specification.groups.update', $category), [
        'name' => 'Face and Body',
        'description' => 'Updated category specification group',
        'icon' => 'ri-leaf-line',
        'is_active' => '0',
    ])->assertRedirect(route('admin.product-specification.groups'));
    expect($category->fresh()->name)->toBe('Face and Body')
        ->and($category->fresh()->is_active)->toBeFalse();

    $this->delete(route('admin.product-specification.groups.destroy', $category))
        ->assertRedirect(route('admin.product-specification.groups'))
        ->assertSessionHas('error');
    expect(Category::whereKey($category->id)->exists())->toBeTrue();

    $empty = specificationCategory('Empty Group');
    $this->delete(route('admin.product-specification.groups.destroy', $empty))->assertRedirect();
    expect(Category::whereKey($empty->id)->exists())->toBeFalse();
});

test('size model exposes explicit cosmetic size tiers', function () {
    expect(Size::whereIn('ml', [10, 30, 50, 100, 250, 500, 1000])->orderBy('ml')->pluck('ml')->all())
        ->toBe([10, 30, 50, 100, 250, 500, 1000]);

    expect((new Size(['ml' => 10]))->size_tier)->toBe('Small')
        ->and((new Size(['ml' => 30]))->size_tier)->toBe('Small')
        ->and((new Size(['ml' => 50]))->size_tier)->toBe('Small')
        ->and((new Size(['ml' => 100]))->size_tier)->toBe('Medium')
        ->and((new Size(['ml' => 250]))->size_tier)->toBe('Medium')
        ->and((new Size(['ml' => 500]))->size_tier)->toBe('Large')
        ->and((new Size(['ml' => 1000]))->size_tier)->toBe('Large');
});
