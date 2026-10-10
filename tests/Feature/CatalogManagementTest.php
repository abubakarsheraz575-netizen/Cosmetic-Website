<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use App\Models\ProductLabel;
use App\Models\ProductSkuVariant;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function catalogProduct(string $name = 'Catalog Serum', array $attributes = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'catalog-category'],
        ['name' => 'Catalog Category', 'icon' => '✦', 'description' => '', 'is_active' => true]
    );

    return Product::create(array_merge([
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
        'description' => 'Catalog test product',
        'category_id' => $category->id,
        'price' => 20,
        'discount' => 0,
        'stock' => 8,
        'emoji' => '✦',
        'is_active' => true,
    ], $attributes));
}

test('admins can browse all catalog management pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = catalogProduct();
    $attribute = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);
    $collection = ProductCollection::create([
        'name' => 'Autumn',
        'starts_at' => now(),
        'ends_at' => now()->addWeek(),
    ]);
    $collection->products()->attach($product);
    $label = ProductLabel::create(['name' => 'Top picks', 'type' => 'hot']);
    $label->products()->attach($product);

    foreach ([
        'admin.ecommerce.prices',
        'admin.ecommerce.inventory',
        'admin.ecommerce.categories',
        'admin.ecommerce.categories.show' => Category::firstOrFail(),
        'admin.ecommerce.attributes',
        'admin.ecommerce.collections',
        'admin.ecommerce.labels',
    ] as $route => $parameter) {
        $url = is_int($route) ? route($parameter) : route($route, $parameter);
        $this->actingAs($admin)->get($url)->assertOk();
    }

    $this->actingAs($admin)->get(route('admin.ecommerce.categories.show', Category::firstOrFail()))
        ->assertSee($product->name);
    $this->actingAs($admin)->get(route('admin.ecommerce.attributes'))
        ->assertSee($attribute->name)
        ->assertSee('options[', false);
});

test('admins can open dedicated collection and label creation pages from their indexes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = catalogProduct('Creation page product');
    $this->actingAs($admin);

    $this->get(route('admin.ecommerce.collections'))
        ->assertOk()
        ->assertSee(route('admin.ecommerce.collections.create'), false);
    $this->get(route('admin.ecommerce.collections.create'))
        ->assertOk()
        ->assertSee('Schedule a collection')
        ->assertSee($product->name);

    $this->get(route('admin.ecommerce.labels'))
        ->assertOk()
        ->assertSee(route('admin.ecommerce.labels.create'), false);
    $this->get(route('admin.ecommerce.labels.create'))
        ->assertOk()
        ->assertSee('Create a product label')
        ->assertSee($product->name);
});

test('collection management explains when selected products are hidden from the storefront', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $unpublished = catalogProduct('Unpublished Collection Serum', ['is_active' => false]);
    $pending = catalogProduct('Pending Collection Serum', ['review_status' => 'pending']);
    $collection = ProductCollection::create([
        'name' => 'Visibility check',
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addDay(),
    ]);
    $collection->products()->attach([$unpublished->id, $pending->id]);

    $this->actingAs($admin)->get(route('admin.ecommerce.collections'))
        ->assertOk()
        ->assertSee('Collections appear on the homepage only while scheduled')
        ->assertSee('Approved · unpublished')
        ->assertSee('Pending review')
        ->assertSee('Not shown on storefront');
});

test('collection schedule timestamps preserve timezone offsets and cannot end in the past', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = catalogProduct();
    $startsAt = now()->subHour();
    $endsAt = now()->addHour();

    $this->actingAs($admin)->post(route('admin.ecommerce.collections.store'), [
        'name' => 'Timezone-aware edit',
        'starts_at' => $startsAt->toIso8601String(),
        'ends_at' => $endsAt->toIso8601String(),
        'products' => [$product->id],
    ])->assertRedirect(route('admin.ecommerce.collections'));

    $collection = ProductCollection::where('name', 'Timezone-aware edit')->firstOrFail();
    expect($collection->starts_at->timestamp)->toBe($startsAt->timestamp)
        ->and($collection->ends_at->timestamp)->toBe($endsAt->timestamp);

    $this->actingAs($admin)->post(route('admin.ecommerce.collections.store'), [
        'name' => 'Already expired edit',
        'starts_at' => now()->subHours(2)->toIso8601String(),
        'ends_at' => now()->subHour()->toIso8601String(),
        'products' => [$product->id],
    ])->assertSessionHasErrors('ends_at');

    expect(ProductCollection::where('name', 'Already expired edit')->exists())->toBeFalse();
});

test('admins can bulk update prices and inventory', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $first = catalogProduct('Serum One');
    $second = catalogProduct('Serum Two');
    $variantProduct = catalogProduct('Serum with shades');
    $attribute = ProductAttribute::create(['name' => 'Shade', 'type' => 'text']);
    $option = ProductVariant::create([
        'product_id' => $variantProduct->id,
        'product_attribute_id' => $attribute->id,
        'value' => 'Rose',
    ]);
    $sellableVariant = ProductSkuVariant::create([
        'product_id' => $variantProduct->id,
        'sku' => 'TEST-ROSE',
        'barcode' => 'TESTROSE01',
        'stock' => 8,
    ]);
    $sellableVariant->options()->sync([$option->id]);

    $this->actingAs($admin)->patch(route('admin.ecommerce.prices.update'), [
        'products' => [
            $first->id => [
                'price' => 32.50,
                'sale_price' => 29.99,
                'sale_starts_at' => now()->subMinute()->format('Y-m-d\TH:i'),
                'sale_ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ],
            $second->id => ['price' => 18, 'sale_price' => '', 'sale_starts_at' => '', 'sale_ends_at' => ''],
        ],
    ])->assertRedirect(route('admin.ecommerce.prices'));

    $this->actingAs($admin)->patch(route('admin.ecommerce.inventory.update'), [
        'products' => [
            $first->id => ['stock' => 4],
            $second->id => ['stock' => 21],
        ],
        'sku_variants' => [
            $sellableVariant->id => ['stock' => 31],
        ],
    ])->assertRedirect(route('admin.ecommerce.inventory'));

    expect((float) $first->fresh()->price)->toBe(32.5)
        ->and((float) $first->fresh()->sale_price)->toBe(29.99)
        ->and((float) $first->fresh()->discount)->toBe(0.0)
        ->and($first->fresh()->stock)->toBe(4)
        ->and($second->fresh()->stock)->toBe(21)
        ->and($sellableVariant->fresh()->stock)->toBe(31)
        ->and($variantProduct->fresh()->stock)->toBe(31);
});

test('admins can start randomized timed sales without replacing an existing sale', function () {
    $saleStart = now()->startOfSecond();
    $this->travelTo($saleStart);
    $admin = User::factory()->create(['role' => 'admin']);
    $eligible = catalogProduct('Eligible Random Sale', ['review_status' => 'approved']);
    $alreadyScheduled = catalogProduct('Protected Scheduled Sale', [
        'review_status' => 'approved',
        'sale_price' => 15,
        'sale_starts_at' => now()->addDay(),
        'sale_ends_at' => now()->addDays(3),
    ]);
    $pending = catalogProduct('Pending Random Sale', ['review_status' => 'pending']);

    $this->actingAs($admin)->post(route('admin.ecommerce.prices.random-sales'))
        ->assertRedirect(route('admin.ecommerce.prices'))
        ->assertSessionHas('success');

    $eligible->refresh();
    expect($eligible->sale_price)->toBeLessThan(20.0)
        ->and($eligible->sale_price)->toBeGreaterThanOrEqual(14.0)
        ->and($eligible->sale_price)->toBeLessThanOrEqual(19.0)
        ->and($eligible->sale_starts_at->timestamp)->toBe($saleStart->timestamp)
        ->and($eligible->sale_ends_at->timestamp)->toBeGreaterThanOrEqual($saleStart->copy()->addDay()->timestamp)
        ->and($eligible->sale_ends_at->timestamp)->toBeLessThanOrEqual($saleStart->copy()->addDays(7)->timestamp)
        ->and((float) $alreadyScheduled->fresh()->sale_price)->toBe(15.0)
        ->and($pending->fresh()->sale_price)->toBeNull();
});

test('inventory distinguishes out of stock products from low and healthy stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    catalogProduct('Unavailable product', ['stock' => 0]);
    catalogProduct('Low stock product', ['stock' => 3]);
    catalogProduct('Available product', ['stock' => 12]);

    $this->actingAs($admin)->get(route('admin.ecommerce.inventory'))
        ->assertOk()
        ->assertSee('Out of stock')
        ->assertSee('Low stock')
        ->assertSee('In stock')
        ->assertSee('1 out of stock')
        ->assertSee('1 low stock');
});

test('admins can replace product options and safely remove old images', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $product = catalogProduct();
    $this->actingAs($admin)->post(route('admin.ecommerce.attributes.store'), [
        'name' => 'Shade',
        'type' => 'color',
    ])->assertRedirect(route('admin.ecommerce.attributes'));
    $attribute = ProductAttribute::where('name', 'Shade')->firstOrFail();
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pKcAAAAASUVORK5CYII=');
    $this->actingAs($admin)->post(route('admin.ecommerce.attributes.variants'), [
        'product_id' => $product->id,
        'product_attribute_id' => $attribute->id,
        'options' => [
            ['value' => 'Rose', 'color_hex' => '#aa3355', 'image' => UploadedFile::fake()->createWithContent('rose.png', $png)],
            ['value' => 'Berry', 'color_hex' => '#663344'],
        ],
    ])->assertRedirect();
    $rose = $product->variants()->where('value', 'Rose')->firstOrFail();
    $path = $rose->image_path;
    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
    $this->actingAs($admin)->get(route('admin.ecommerce.attributes'))
        ->assertOk()
        ->assertSee('Rose option')
        ->assertSee(asset('storage/'.$path));

    $this->actingAs($admin)->post(route('admin.ecommerce.attributes.variants'), [
        'product_id' => $product->id,
        'product_attribute_id' => $attribute->id,
        'options' => [
            ['id' => $rose->id, 'value' => 'Rose', 'color_hex' => '#aa3355', 'remove_image' => '1'],
        ],
    ])->assertRedirect();

    expect($product->variants()->where('product_attribute_id', $attribute->id)->pluck('value')->all())
        ->toBe(['Rose']);
    Storage::disk('public')->assertMissing($path);
});

test('admins can edit attributes and delete their assigned options and unused variant images', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $product = catalogProduct();
    $attribute = ProductAttribute::create(['name' => 'Finish', 'type' => 'text']);

    $this->actingAs($admin)->patch(route('admin.ecommerce.attributes.update', $attribute), [
        'name' => 'Texture',
        'type' => 'size',
    ])->assertRedirect(route('admin.ecommerce.attributes'));
    expect($attribute->fresh()->name)->toBe('Texture')
        ->and($attribute->fresh()->type)->toBe('size');

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pKcAAAAASUVORK5CYII=');
    $image = UploadedFile::fake()->createWithContent('small.png', $png);
    $this->actingAs($admin)->post(route('admin.ecommerce.attributes.variants'), [
        'product_id' => $product->id,
        'product_attribute_id' => $attribute->id,
        'options' => [['value' => 'Small', 'image' => $image]],
    ])->assertRedirect();
    $variantImage = $product->variants()->where('product_attribute_id', $attribute->id)->firstOrFail()->image_path;
    Storage::disk('public')->assertExists($variantImage);

    $this->actingAs($admin)->delete(route('admin.ecommerce.attributes.destroy', $attribute))
        ->assertRedirect(route('admin.ecommerce.attributes'));
    expect(ProductAttribute::find($attribute->id))->toBeNull()
        ->and($product->variants()->count())->toBe(0);
    Storage::disk('public')->assertMissing($variantImage);
});

test('admins can create and remove scheduled collections and label sets', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = catalogProduct();

    $this->actingAs($admin)->post(route('admin.ecommerce.collections.store'), [
        'name' => 'Weekend edit',
        'description' => 'A timed product edit',
        'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'products' => [$product->id],
    ])->assertRedirect(route('admin.ecommerce.collections'));

    $collection = ProductCollection::firstOrFail();
    expect($collection->products()->pluck('products.id')->all())->toBe([$product->id]);
    $this->actingAs($admin)->delete(route('admin.ecommerce.collections.destroy', $collection))->assertRedirect();
    expect(ProductCollection::count())->toBe(0);

    $this->actingAs($admin)->post(route('admin.ecommerce.labels.store'), [
        'name' => 'Summer spotlight',
        'type' => 'new',
        'products' => [$product->id],
    ])->assertRedirect(route('admin.ecommerce.labels'));

    $label = ProductLabel::firstOrFail();
    expect($label->products()->pluck('products.id')->all())->toBe([$product->id])
        ->and($label->type)->toBe('new');
    $this->actingAs($admin)->delete(route('admin.ecommerce.labels.destroy', $label))->assertRedirect();
    expect(ProductLabel::count())->toBe(0);
});
