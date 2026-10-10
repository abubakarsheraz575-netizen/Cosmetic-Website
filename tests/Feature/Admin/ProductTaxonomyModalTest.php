<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Support\InternalBarcode;

test('product create and edit pages share the category and size quick-create modal', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (['/admin/products', '/admin/products/create'] as $path) {
        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertSee('id="quickTaxonomyModal"', false)
            ->assertSee('quickTaxonomyOpen', false)
            ->assertSee('Add Category', false)
            ->assertSee('Add size', false)
            ->assertSee('ri-flask-line', false);
    }
});

test('product edit modal matches the category modal and uses the shared icon choices', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertOk()
        ->assertSee('id="prodModal"', false)
        ->assertSee('aria-labelledby="modalTitle"', false)
        ->assertSee('class="nb-modal-head"', false)
        ->assertSee('class="nb-modal-body"', false)
        ->assertSee('id="prodIconGrid"', false)
        ->assertSee('ri-flask-line', false)
        ->assertSee('ri-sparkling-2-line', false);
});

test('quick taxonomy creation returns the saved category and size for product forms', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $categoryResponse = $this->actingAs($admin)
        ->postJson(route('admin.categories.store'), [
            'name' => 'Botanical Skincare',
            'icon' => '🌿',
            'is_active' => true,
        ])
        ->assertOk()
        ->assertJsonPath('category.name', 'Botanical Skincare')
        ->assertJsonPath('category.is_active', true);

    $sizeResponse = $this->postJson(route('admin.sizes.store'), [
        'name' => 'Travel Size',
        'ml' => 30,
        'is_active' => true,
    ])
        ->assertOk()
        ->assertJsonPath('size.name', 'Travel Size')
        ->assertJsonPath('size.ml', 30);

    $this->assertDatabaseHas('categories', ['id' => $categoryResponse->json('category.id'), 'is_active' => true]);
    $this->assertDatabaseHas('sizes', ['id' => $sizeResponse->json('size.id'), 'is_active' => true]);
});

test('product creation supports inline brand and tracks sellable option stock with legacy discounts', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Inline Product Category',
        'slug' => 'inline-product-category',
        'is_active' => true,
    ]);

    $brand = $this->actingAs($admin)
        ->postJson(route('admin.products.quick-brand'), ['name' => 'Inline Beauty'])
        ->assertOk()
        ->json('brand');
    $attribute = $this->postJson(route('admin.products.quick-attribute'), [
        'name' => 'Finish',
        'type' => 'text',
    ])->assertOk()->json('attribute');

    $response = $this->postJson(route('admin.products.store'), [
        'name' => 'Inline Rose Serum',
        'category_id' => $category->id,
        'brand_id' => $brand['id'],
        'price' => 45,
        'discount_type' => 'fixed',
        'discount' => 5.50,
        'stock' => 12,
        'variants' => [[
            'product_attribute_id' => $attribute['id'],
            'value' => 'Soft matte',
            'color_hex' => '#aabbcc',
        ]],
        'sku_variants' => [[
            'stock' => 12,
            'options' => [[
                'product_attribute_id' => $attribute['id'],
                'value' => 'Soft matte',
            ]],
        ]],
    ])->assertOk()
        ->assertJsonPath('product.discount_type', 'fixed')
        ->assertJsonPath('product.brand_id', $brand['id']);

    $product = Product::findOrFail($response->json('product.id'));
    expect($product->final_price)->toBe(39.5)
        ->and($product->brand->name)->toBe('Inline Beauty')
        ->and($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->value)->toBe('Soft matte')
        ->and($product->skuVariants)->toHaveCount(1)
        ->and($product->skuVariants->first()->stock)->toBe(12)
        ->and($product->sku)->toBe('COS-'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT))
        ->and($product->barcode)->toBe(InternalBarcode::forProductId((int) $product->id));
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'discount_type' => 'fixed',
        'discount' => 5.50,
    ]);
    $this->assertDatabaseHas('brands', ['id' => $brand['id'], 'is_active' => true]);
    $this->assertDatabaseHas('product_attributes', ['id' => $attribute['id'], 'type' => 'text']);
});

test('product creation rejects discounts above the price and duplicate option values', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Discount Validation Category',
        'slug' => 'discount-validation-category',
        'is_active' => true,
    ]);
    $attribute = ProductAttribute::create(['name' => 'Shade', 'type' => 'color']);
    $base = [
        'name' => 'Validated Product',
        'category_id' => $category->id,
        'price' => 20,
        'discount_type' => 'fixed',
        'discount' => 21,
        'stock' => 1,
    ];

    $this->actingAs($admin)
        ->postJson(route('admin.products.store'), $base)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('discount');

    $base['discount'] = 1;
    $base['variants'] = [
        ['product_attribute_id' => $attribute->id, 'value' => 'Rose'],
        ['product_attribute_id' => $attribute->id, 'value' => ' rose '],
    ];
    $this->postJson(route('admin.products.store'), $base)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('variants');

    $this->assertDatabaseMissing('products', ['name' => 'Validated Product']);
});

test('additional category icon choices are available in admin category and product forms', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $choices = config('product_icons.choices');

    expect(count($choices))->toBeGreaterThan(8);

    $this->actingAs($admin)
        ->get('/admin/categories')
        ->assertOk()
        ->assertSee('Herbal Care')
        ->assertSee('Skin Repair');

    $this->actingAs($admin)
        ->get('/admin/products/create')
        ->assertOk()
        ->assertSee('Herbal Care')
        ->assertSee('Skin Repair');
});
