<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductAttribute;
use App\Models\ProductLabel;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Support\InternalBarcode;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function commerceWorkflowCategory(): Category
{
    return Category::create([
        'name' => 'Commerce Workflow',
        'slug' => 'commerce-workflow',
        'is_active' => true,
    ]);
}

test('product creation accepts the empty cover field from the form when no photos are selected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'No Photo Product',
        'category_id' => $category->id,
        'price' => 19.99,
        'stock' => 3,
        'cover_image_index' => '',
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    expect($product->review_status)->toBe('approved')
        ->and($product->images)->toBeEmpty();

    $this->get(route('home'))->assertOk()->assertSee($product->name);
    $this->get(route('shop'))->assertOk()->assertSee($product->name);
});

test('product creation shows the Hot, New, and Sale badge choices with centered dropdown indicators', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Hot')
        ->assertSee('New')
        ->assertSee('Sale')
        ->assertDontSee('Sale price (£)')
        ->assertSee('name="sale_starts_at"', false)
        ->assertSee('name="sale_ends_at"', false)
        ->assertSee('name="sale_discount"', false)
        ->assertSee('name="sale_discount_type"', false)
        ->assertSee('Media library')
        ->assertSee('class="variant-image-pick"', false)
        ->assertSee('name="variants[__INDEX__][image_path]"', false)
        ->assertSee('class="fld variant-description"', false)
        ->assertSee('name="variants[__INDEX__][description]"', false)
        ->assertSee('background-position:right 14px 50%', false);
});

test('product creation saves selected Hot, New, and Sale storefront badges', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Badge Preview Serum',
        'category_id' => $category->id,
        'price' => 19.99,
        'stock' => 3,
        'label_types' => ['hot', 'new', 'sale'],
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    expect($product->labels->pluck('type')->sort()->values()->all())->toBe(['hot', 'new', 'sale'])
        ->and($product->labels->pluck('name')->sort()->values()->all())->toBe(['Hot', 'New', 'Sale']);
});

test('product creation saves scheduled sale, store label, generated identifiers, and package measurements', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $label = ProductLabel::create(['name' => 'New Arrival', 'type' => 'new']);
    $starts = now()->subHour()->format('Y-m-d\TH:i');
    $ends = now()->addHour()->format('Y-m-d\TH:i');

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Timed Beauty Serum',
        'category_id' => $category->id,
        'price' => 48,
        'sale_price' => 36.5,
        'sale_starts_at' => $starts,
        'sale_ends_at' => $ends,
        'stock' => 8,
        'shipping_weight' => 0.24,
        'weight_unit' => 'kg',
        'shipping_length' => 12.5,
        'shipping_width' => 6,
        'shipping_height' => 4,
        'dimension_unit' => 'cm',
        'labels' => [$label->id],
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    expect($product->final_price)->toBe(36.5)
        ->and($product->is_on_sale)->toBeTrue()
        ->and($product->sku)->toBe('COS-'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT))
        ->and($product->barcode)->toBe(InternalBarcode::forProductId((int) $product->id))
        ->and($product->weight_unit)->toBe('kg')
        ->and($product->dimension_unit)->toBe('cm')
        ->and($product->labels->first()->is($label))->toBeTrue();

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('Timed Beauty Serum')
        ->assertSee('New Arrival')
        ->assertSee('-24%');

    $this->travelTo(now()->addDays(2));
    expect($product->fresh()->final_price)->toBe(48.0)
        ->and($product->fresh()->is_on_sale)->toBeFalse();
    $this->travelBack();
});

test('scheduled percentage sale overrides the regular discount only during its sale window', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $starts = now()->addHour();
    $ends = now()->addHours(2);

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Scheduled Discount Serum',
        'category_id' => $category->id,
        'price' => 48,
        'discount' => 5,
        'discount_type' => 'fixed',
        'sale_discount' => 25,
        'sale_discount_type' => 'percent',
        'sale_starts_at' => $starts->format('Y-m-d\TH:i'),
        'sale_ends_at' => $ends->format('Y-m-d\TH:i'),
        'stock' => 4,
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    expect($product->sale_discount)->toBe(25.0)
        ->and($product->sale_discount_type)->toBe('percent')
        ->and($product->final_price)->toBe(43.0);

    $this->travelTo($starts->copy()->addMinutes(1));
    expect($product->fresh()->final_price)->toBe(36.0)
        ->and($product->fresh()->is_on_sale)->toBeTrue();

    $this->travelTo($ends->copy()->addMinute());
    expect($product->fresh()->final_price)->toBe(43.0);
    $this->travelBack();
});

test('scheduled fixed sale discount is stored independently from regular percentage discount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $starts = now()->subHour();
    $ends = now()->addHour();

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Fixed Scheduled Sale Serum',
        'category_id' => $category->id,
        'price' => 48,
        'discount' => 10,
        'discount_type' => 'percent',
        'sale_discount' => 7.5,
        'sale_discount_type' => 'fixed',
        'sale_starts_at' => $starts->format('Y-m-d\TH:i'),
        'sale_ends_at' => $ends->format('Y-m-d\TH:i'),
        'stock' => 4,
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    expect($product->discount)->toBe(10.0)
        ->and($product->discount_type)->toBe('percent')
        ->and($product->sale_discount)->toBe(7.5)
        ->and($product->sale_discount_type)->toBe('fixed')
        ->and($product->final_price)->toBe(40.5);
});

test('scheduled sale discount requires a complete valid window and a valid discount amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $starts = now()->addHour()->format('Y-m-d\TH:i');
    $ends = now()->addHours(2)->format('Y-m-d\TH:i');
    $payload = [
        'name' => 'Invalid Scheduled Sale Serum',
        'category_id' => $category->id,
        'price' => 48,
        'discount' => 0,
        'discount_type' => 'percent',
        'sale_discount' => 20,
        'sale_discount_type' => 'percent',
        'sale_starts_at' => $starts,
        'sale_ends_at' => $ends,
        'stock' => 4,
        'is_active' => true,
    ];

    $this->actingAs($admin)->postJson(route('admin.products.store'), [
        ...$payload,
        'sale_ends_at' => '',
    ])->assertUnprocessable()->assertJsonValidationErrors('sale_ends_at');

    $this->actingAs($admin)->postJson(route('admin.products.store'), [
        ...$payload,
        'sale_discount' => 101,
    ])->assertUnprocessable()->assertJsonValidationErrors('sale_discount');

    $this->actingAs($admin)->postJson(route('admin.products.store'), [
        ...$payload,
        'sale_discount' => 48.01,
        'sale_discount_type' => 'fixed',
    ])->assertUnprocessable()->assertJsonValidationErrors('sale_discount');
});

test('product edit form saves shipping package weight and dimensions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $product = Product::create([
        'name' => 'Editable Package Lotion',
        'slug' => 'editable-package-lotion',
        'category_id' => $category->id,
        'price' => 25,
        'stock' => 4,
        'is_active' => true,
        'review_status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Shipping package')
        ->assertSee('name="shipping_weight"', false);

    $this->patchJson(route('admin.products.update', $product), [
        'name' => $product->name,
        'category_id' => $category->id,
        'price' => 25,
        'stock' => 4,
        'shipping_weight' => 750,
        'weight_unit' => 'g',
        'shipping_length' => 12,
        'shipping_width' => 8,
        'shipping_height' => 4,
        'dimension_unit' => 'cm',
        'is_active' => true,
    ])->assertOk();

    expect($product->fresh()->shipping_weight)->toBe(750.0)
        ->and($product->fresh()->weight_unit)->toBe('g')
        ->and($product->fresh()->shipping_length)->toBe(12.0);
});

test('admin product creation can select the storefront photo from the local media library', function () {
    Storage::fake('public');
    Storage::disk('public')->put('media/serum-front.jpg', 'image-data');
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $this->actingAs($admin)->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('Choose from Media library');

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Media Library Serum',
        'category_id' => $category->id,
        'price' => 20,
        'stock' => 4,
        'image_paths' => ['media/serum-front.jpg'],
        'cover_image_index' => 0,
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    $image = ProductImage::where('product_id', $product->id)->firstOrFail();
    expect($image->path)->toBe('media/serum-front.jpg')
        ->and($image->is_cover)->toBeTrue();

    $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Storefront photo')
        ->assertSee('Choose from Media library')
        ->assertDontSee('Emoji icon');

    Storage::disk('public')->put('media/serum-back.jpg', 'image-data');
    $this->patchJson(route('admin.products.cover', $product), [
        'cover_image_path' => 'media/serum-back.jpg',
    ])->assertOk();
    expect($product->fresh()->images->first()->path)->toBe('media/serum-back.jpg')
        ->and($product->fresh()->images->first()->is_cover)->toBeTrue();
});

test('admin can add media and uploaded photos to a product gallery up to six total and choose the storefront photo', function () {
    Storage::fake('public');
    foreach (['one.jpg', 'two.jpg', 'three.jpg', 'four.jpg', 'five.jpg', 'six.jpg', 'seven.jpg'] as $name) {
        Storage::disk('public')->put('media/'.$name, 'image-data');
    }
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create([
        'name' => 'Gallery Test Serum',
        'slug' => 'gallery-test-serum',
        'category_id' => commerceWorkflowCategory()->id,
        'price' => 20,
        'stock' => 5,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    $cover = ProductImage::create([
        'product_id' => $product->id,
        'path' => 'media/one.jpg',
        'sort_order' => 0,
        'is_cover' => true,
    ]);

    $this->actingAs($admin)->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Product photo gallery')
        ->assertSee('gallery_uploads[]', false)
        ->assertSee('Choose from Media library');

    $this->patchJson(route('admin.products.images.update', $product), [
        'image_paths' => ['media/two.jpg', 'media/three.jpg'],
        'gallery_uploads' => [\Illuminate\Http\UploadedFile::fake()->createWithContent(
            'four.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAAEklEQVR4nGPUqNjCwMDAxAAGAA+OAVjFi9u4AAAAAElFTkSuQmCC')
        )],
        'cover_new_image_index' => 2,
    ])->assertOk()->assertJsonPath('message', 'Product gallery updated.');
    expect($product->fresh()->images)->toHaveCount(4)
        ->and($product->fresh()->images->firstWhere('is_cover', true)->path)->toBe('media/three.jpg');

    $this->patchJson(route('admin.products.images.update', $product), [
        'image_paths' => ['media/five.jpg', 'media/six.jpg', 'media/seven.jpg'],
    ])->assertUnprocessable();
    expect($product->fresh()->images)->toHaveCount(4);

    $this->patchJson(route('admin.products.images.update', $product), [
        'remove_image_ids' => [$cover->id],
        'image_paths' => ['media/five.jpg', 'media/six.jpg', 'media/seven.jpg'],
    ])->assertOk();
    expect($product->fresh()->images)->toHaveCount(6)
        ->and($product->fresh()->images->filter(fn ($image) => $image->is_cover))->toHaveCount(1);

    $this->patchJson(route('admin.products.cover', $product), [
        'cover_image_path' => 'media/seven.jpg',
    ])->assertUnprocessable();
    expect($product->fresh()->images)->toHaveCount(6);
});

test('admin product catalog shows selected product photos in its image column', function () {
    Storage::fake('public');
    Storage::disk('public')->put('media/catalog-photo.jpg', 'image-data');
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Catalog Photo Serum',
        'category_id' => $category->id,
        'price' => 20,
        'stock' => 4,
        'image_paths' => ['media/catalog-photo.jpg'],
        'cover_image_index' => 0,
        'is_active' => true,
    ])->assertOk();

    $product = Product::findOrFail($response->json('product.id'));
    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('storage/media/catalog-photo.jpg')
        ->assertSee($product->name);
});

test('product creation accepts six library photos and a library photo for a sellable option', function () {
    Storage::fake('public');
    $photoPaths = [];
    foreach (range(1, 6) as $index) {
        $path = "media/serum-{$index}.jpg";
        Storage::disk('public')->put($path, 'image-data');
        $photoPaths[] = $path;
    }
    Storage::disk('public')->put('media/rose-option.webp', 'image-data');

    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $finish = ProductAttribute::create(['name' => 'Finish', 'type' => 'text']);

    $response = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Six Photo Serum',
        'category_id' => $category->id,
        'price' => 20,
        'stock' => 0,
        'image_paths' => $photoPaths,
        'cover_image_index' => 4,
        'variants' => [[
            'product_attribute_id' => $finish->id,
            'value' => 'Rose',
            'description' => 'Warm rose with a satin finish.',
            'image_path' => 'media/rose-option.webp',
        ]],
        'sku_variants' => [[
            'stock' => 4,
            'options' => [[
                'product_attribute_id' => $finish->id,
                'value' => 'Rose',
            ]],
        ]],
        'is_active' => true,
    ])->assertOk();

    $product = Product::with(['images', 'variants'])->findOrFail($response->json('product.id'));
    expect($product->images)->toHaveCount(6)
        ->and($product->images->firstWhere('path', $photoPaths[4])->is_cover)->toBeTrue()
        ->and($product->variants->first()->image_path)->toBe('media/rose-option.webp')
        ->and($product->variants->first()->description)->toBe('Warm rose with a satin finish.');

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('rose-option.webp')
        ->assertSee('Warm rose with a satin finish.')
        ->assertDontSee('<select id="pdOption', false)
        ->assertSee('serum-5.jpg');
});

test('product creation rejects option photos that are not in the media library', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $finish = ProductAttribute::create(['name' => 'Finish', 'type' => 'text']);

    $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Missing Option Photo',
        'category_id' => $category->id,
        'price' => 20,
        'stock' => 0,
        'variants' => [[
            'product_attribute_id' => $finish->id,
            'value' => 'Rose',
            'image_path' => 'media/not-in-library.jpg',
        ]],
    ])->assertUnprocessable()->assertJsonValidationErrors('variants.0.image_path');
});

test('attribute management updates option details without breaking linked sellable variants', function () {
    Storage::fake('public');
    Storage::disk('public')->put('media/coral.jpg', 'image-data');
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $color = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);
    $product = Product::create([
        'name' => 'Linked Shade Lipstick',
        'slug' => 'linked-shade-lipstick',
        'category_id' => $category->id,
        'price' => 18,
        'stock' => 3,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    $option = $product->variants()->create([
        'product_attribute_id' => $color->id,
        'value' => 'Coral',
        'color_hex' => '#D35F66',
    ]);
    $skuVariant = $product->skuVariants()->create([
        'sku' => 'LINKED-CORAL',
        'barcode' => '1234567890123',
        'stock' => 3,
    ]);
    $skuVariant->options()->attach($option->id);

    $this->actingAs($admin)->get(route('admin.ecommerce.attributes', [
        'product_id' => $product->id,
        'attribute_id' => $color->id,
    ]))->assertOk()
        ->assertSee('Assign product options');

    $this->post(route('admin.ecommerce.attributes.variants'), [
        'product_id' => $product->id,
        'product_attribute_id' => $color->id,
        'options' => [[
            'id' => $option->id,
            'value' => 'Coral Bloom',
            'description' => 'Soft coral, satin finish.',
            'color_hex' => '#D35F66',
            'image_path' => 'media/coral.jpg',
        ]],
    ])->assertSessionHasNoErrors()->assertRedirect();

    $freshOption = $option->fresh();
    expect($freshOption->value)->toBe('Coral Bloom')
        ->and($freshOption->description)->toBe('Soft coral, satin finish.')
        ->and($freshOption->image_path)->toBe('media/coral.jpg')
        ->and($skuVariant->fresh()->options->first()->id)->toBe($option->id);

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('Soft coral, satin finish.')
        ->assertSee('Coral Bloom');
});

test('attribute management blocks deleting option values used by sellable stock combinations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $color = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);
    $product = Product::create([
        'name' => 'Protected Shade Product',
        'slug' => 'protected-shade-product',
        'category_id' => $category->id,
        'price' => 18,
        'stock' => 1,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    $option = $product->variants()->create([
        'product_attribute_id' => $color->id,
        'value' => 'Coral',
    ]);
    $skuVariant = $product->skuVariants()->create([
        'sku' => 'PROTECTED-CORAL',
        'barcode' => '1234567890124',
        'stock' => 1,
    ]);
    $skuVariant->options()->attach($option->id);

    $this->actingAs($admin)->post(route('admin.ecommerce.attributes.variants'), [
        'product_id' => $product->id,
        'product_attribute_id' => $color->id,
        'options' => [],
    ])->assertSessionHasErrors('options');

    expect($option->fresh())->not->toBeNull()
        ->and($skuVariant->fresh()->options)->toHaveCount(1);
});

test('only signed-in customers can add variants to their bag and variant quantities stay independent', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $color = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);
    $size = ProductAttribute::create(['name' => 'Size', 'type' => 'size']);
    $options = [
        ['product_attribute_id' => $color->id, 'value' => 'Rose'],
        ['product_attribute_id' => $color->id, 'value' => 'Nude'],
        ['product_attribute_id' => $size->id, 'value' => 'Mini'],
        ['product_attribute_id' => $size->id, 'value' => 'Full'],
    ];
    $combination = fn (string $shade, string $volume, int $stock) => [
        'stock' => $stock,
        'options' => [
            ['product_attribute_id' => $color->id, 'value' => $shade],
            ['product_attribute_id' => $size->id, 'value' => $volume],
        ],
    ];

    $created = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Optioned Face Cream',
        'category_id' => $category->id,
        'price' => 28,
        'stock' => 0,
        'variants' => $options,
        'sku_variants' => [
            $combination('Rose', 'Mini', 3),
            $combination('Rose', 'Full', 5),
            $combination('Nude', 'Mini', 2),
            $combination('Nude', 'Full', 4),
        ],
        'is_active' => true,
    ])->assertOk();
    $product = Product::with('skuVariants.options.attribute')->findOrFail($created->json('product.id'));
    $selected = $product->skuVariants->first();

    auth()->logout();
    $this->postJson(route('cart.add'), ['product_id' => $product->id])
        ->assertUnauthorized();

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.add'), ['product_id' => $product->id])
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');

    $this->postJson(route('cart.add'), [
        'product_id' => $product->id,
        'sku_variant_id' => $selected->id,
        'qty' => 2,
    ])->assertOk()->assertJsonPath('status', 'success');

    $this->getJson(route('cart.items'))
        ->assertOk()
        ->assertJsonPath('count', 2)
        ->assertJsonPath('items.0.variant_id', $selected->id)
        ->assertJsonPath('items.0.qty', 2)
        ->assertJsonPath('items.0.stock', $selected->stock);

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('data-attribute-id="'.$color->id.'"', false)
        ->assertSee('data-attribute-id="'.$size->id.'"', false)
        ->assertSee('sku_variant_id');
});

test('signed-in customer orders snapshot selected option and package data and decrement its stock once', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = commerceWorkflowCategory();
    $color = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);
    $created = $this->actingAs($admin)->postJson(route('admin.products.store'), [
        'name' => 'Checkout Shade Cream',
        'category_id' => $category->id,
        'price' => 28,
        'stock' => 0,
        'shipping_weight' => 0.25,
        'weight_unit' => 'kg',
        'shipping_length' => 10,
        'shipping_width' => 8,
        'shipping_height' => 5,
        'dimension_unit' => 'cm',
        'variants' => [[
            'product_attribute_id' => $color->id,
            'value' => 'Rose',
        ]],
        'sku_variants' => [[
            'stock' => 3,
            'options' => [[
                'product_attribute_id' => $color->id,
                'value' => 'Rose',
            ]],
        ]],
        'is_active' => true,
    ])->assertOk();
    $product = Product::with('skuVariants')->findOrFail($created->json('product.id'));
    $variant = $product->skuVariants->first();

    auth()->logout();
    $customer = User::factory()->create(['role' => 'user']);
    $this->postJson(route('cart.add'), [
        'product_id' => $product->id,
        'sku_variant_id' => $variant->id,
        'qty' => 2,
    ])->assertUnauthorized();

    $this->actingAs($customer)->postJson(route('cart.add'), [
        'product_id' => $product->id,
        'sku_variant_id' => $variant->id,
        'qty' => 2,
    ])->assertOk();
    $this->get(route('checkout'))->assertOk()->assertSee('Your signed-in account is required');

    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');
    Setting::set('ship_included_weight_kg', '0.1');
    Setting::set('ship_surcharge_per_kg', '2');
    Http::fake(function (ClientRequest $request) {
        if ($request->method() === 'POST') {
            return Http::response(['id' => 'pi_variant_order', 'client_secret' => 'pi_variant_secret'], 200);
        }
        $order = Order::firstOrFail();
        return Http::response([
            'id' => 'pi_variant_order',
            'status' => 'succeeded',
            'amount' => (int) round((float) $order->total * 100),
            'currency' => 'gbp',
            'metadata' => ['order_id' => (string) $order->id],
        ], 200);
    });

    $process = $this->postJson(route('checkout.process'), [
        'name' => 'Registered Shopper',
        'email' => $customer->email,
        'phone' => '123456789',
        'address' => '1 Beauty Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'payment_method' => 'stripe',
    ])->assertOk()->assertJsonPath('status', 'success');

    $order = Order::with('items')->findOrFail($process->json('order_id'));
    expect($order->items)->toHaveCount(1)
        ->and((float) $order->shipping)->toBe(2.0)
        ->and($order->user_id)->toBe($customer->id)
        ->and($order->items->first()->product_sku_variant_id)->toBe($variant->id)
        ->and($order->items->first()->product_sku)->toBe($variant->sku)
        ->and($order->items->first()->product_barcode)->toBe($variant->barcode)
        ->and($order->items->first()->variant_options)->toBe([['attribute' => 'Color', 'value' => 'Rose']])
        ->and((float) $order->items->first()->shipping_weight)->toBe(0.25);

    $confirmation = ['order_id' => $order->id, 'payment_intent' => 'pi_variant_order'];
    $this->postJson(route('checkout.confirm'), $confirmation)->assertOk()->assertJsonPath('status', 'success');
    $this->postJson(route('checkout.confirm'), $confirmation)->assertOk()->assertJsonPath('status', 'success');
    expect($variant->fresh()->stock)->toBe(1)
        ->and($product->fresh()->stock)->toBe(1)
        ->and($order->fresh()->payment_status)->toBe('paid');
});
