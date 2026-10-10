<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use App\Models\ProductLabel;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;

function createMerchandisingProduct(string $name = 'Merchandising Serum'): Product
{
    $category = Category::create([
        'name' => $name.' Category',
        'slug' => strtolower(str_replace(' ', '-', $name)).'-category-'.uniqid(),
        'icon' => '✦',
        'is_active' => true,
    ]);

    return Product::create([
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
        'description' => 'A product for storefront merchandising tests.',
        'category_id' => $category->id,
        'price' => 24,
        'discount' => 0,
        'stock' => 10,
        'emoji' => '✦',
        'is_active' => true,
        'review_status' => 'approved',
    ]);
}

test('homepage latest products includes the newest active product and its image', function () {
    Storage::fake('public');

    $product = createMerchandisingProduct('Latest Glow Serum');
    Storage::disk('public')->put('products/latest-glow-serum.jpg', 'test-image-content');
    $product->images()->create([
        'path' => 'products/latest-glow-serum.jpg',
        'sort_order' => 0,
        'is_cover' => true,
    ]);

    $response = $this->get(route('home'))->assertOk();
    $latestSection = explode('</section>', explode(
        '<section class="latest-sec" id="latest">',
        $response->getContent(),
        2
    )[1], 2)[0];

    expect($latestSection)
        ->toContain($product->name)
        ->toContain(asset('storage/products/latest-glow-serum.jpg'));
});

test('storefront cards show admin product labels', function () {
    $product = createMerchandisingProduct();
    $label = ProductLabel::create(['name' => 'Customer favourite', 'type' => 'hot']);
    $label->products()->attach($product);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Customer favourite');

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('Customer favourite');

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('Customer favourite');
});

test('fixed discounts are preserved in shop data, badges, and price sorting', function () {
    $discounted = createMerchandisingProduct('Fixed Discount Serum');
    $discounted->update([
        'price' => 50,
        'discount' => 10,
        'discount_type' => 'fixed',
    ]);
    $regular = createMerchandisingProduct('Regular Price Serum');
    $regular->update(['price' => 42]);

    $this->getJson(route('shop', ['sort' => 'price_low']))
        ->assertOk()
        ->assertJsonPath('products.0.id', $discounted->id)
        ->assertJsonPath('products.0.discount', 10)
        ->assertJsonPath('products.0.discount_type', 'fixed')
        ->assertJsonPath('products.0.final_price', 40);

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('-£10.00');
    $this->get(route('product.show', $discounted->slug))
        ->assertOk()
        ->assertSee('-£10.00');
});

test('product detail exposes configured color swatches and variant images', function () {
    $product = createMerchandisingProduct('Color Variant Serum');
    $attribute = ProductAttribute::create(['name' => 'Color', 'type' => 'color']);
    ProductVariant::create([
        'product_id' => $product->id,
        'product_attribute_id' => $attribute->id,
        'value' => 'Rose',
        'color_hex' => '#d68191',
        'image_path' => 'product-variants/rose-serum.jpg',
    ]);

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('Color options')
        ->assertSee('data-value="Rose"', false)
        ->assertSee('data-image="'.asset('storage/product-variants/rose-serum.jpg').'"', false)
        ->assertSee('onclick="selectProductVariant(this)"', false);
});

test('floating cart opens the cart drawer without navigating to the current page', function () {
    $product = createMerchandisingProduct('Floating Cart Serum');

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee('<button type="button" class="floating-cart" aria-label="Open cart">', false)
        ->assertDontSee('<a href="#" class="floating-cart"', false)
        ->assertSee("document.addEventListener('click', function (e)", false)
        ->assertSee('openDrawer();', false);
});

test('active collections display on the home page only during their scheduled dates', function () {
    $product = createMerchandisingProduct('Limited Glow Serum');
    $unpublishedProduct = createMerchandisingProduct('Unpublished Glow Serum');
    $unpublishedProduct->update(['is_active' => false, 'review_status' => 'pending']);
    $activeCollection = ProductCollection::create([
        'name' => 'Limited Summer Edit',
        'description' => 'Available for a short time.',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    $activeCollection->products()->attach([$product->id, $unpublishedProduct->id]);
    $expiredCollection = ProductCollection::create([
        'name' => 'Expired Autumn Edit',
        'starts_at' => now()->subDays(3),
        'ends_at' => now()->subDay(),
    ]);
    $expiredCollection->products()->attach($product);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Limited Summer Edit')
        ->assertSee($product->name)
        ->assertDontSee($unpublishedProduct->name)
        ->assertSee('collection-time-unit', false)
        ->assertSee('data-days', false)
        ->assertDontSee('Expired Autumn Edit');
});

test('shop collection cards include a live countdown and paid-sales progress indicator', function () {
    $product = createMerchandisingProduct('Timed Glow Serum');
    $collection = ProductCollection::create([
        'name' => 'Limited Glow Edit',
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addDays(2),
    ]);
    $collection->products()->attach($product);

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('data-countdown-end="'.$collection->ends_at->toIso8601String().'"', false)
        ->assertSee('data-countdown-days', false)
        ->assertSee('product-sold-track', false);

    $this->getJson(route('shop'))
        ->assertOk()
        ->assertJsonPath('products.0.collection_ends_at', $collection->ends_at->toIso8601String())
        ->assertJsonPath('products.0.sold_units', 0);
});

test('expired collections are hidden while their products and stock remain in the shop', function () {
    $product = createMerchandisingProduct('Expired Collection Serum');
    $product->update(['stock' => 7]);
    $collection = ProductCollection::create([
        'name' => 'Expired Glow Edit',
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subMinute(),
    ]);
    $collection->products()->attach($product);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Expired Glow Edit');

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee($product->name)
        ->assertDontSee('data-countdown-end="'.$collection->ends_at->toIso8601String().'"', false);

    $this->getJson(route('shop'))
        ->assertOk()
        ->assertJsonPath('products.0.id', $product->id)
        ->assertJsonPath('products.0.stock', 7)
        ->assertJsonPath('products.0.collection_ends_at', null);
});

test('homepage brand slider uses active admin-managed brand names and logos', function () {
    $brand = Brand::create([
        'name' => 'Glow Studio',
        'slug' => 'glow-studio',
        'logo_path' => 'brands/glow-studio.png',
        'is_active' => true,
    ]);
    Brand::create([
        'name' => 'Hidden Brand',
        'slug' => 'hidden-brand',
        'is_active' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Featured brands')
        ->assertSee($brand->name)
        ->assertSee(asset('storage/' . $brand->logo_path), false)
        ->assertSee("L'Oréal Paris")
        ->assertSee('Maybelline New York')
        ->assertSee('M·A·C Cosmetics')
        ->assertSee('The Ordinary')
        ->assertSee('NYX Professional Makeup')
        ->assertDontSee('Hidden Brand');
});

test('shop and product details display brands and shop filtering stays available over ajax', function () {
    $brand = Brand::create([
        'name' => 'Studio Glow',
        'slug' => 'studio-glow',
        'logo_path' => 'brands/studio-glow.png',
        'is_active' => true,
    ]);
    $product = createMerchandisingProduct('Studio Glow Serum');
    $brand->products()->save($product);
    $otherProduct = createMerchandisingProduct('Independent Serum');

    $this->get(route('shop'))
        ->assertOk()
        ->assertSee('Shop by brand')
        ->assertSee($brand->name)
        ->assertSee(asset('storage/' . $brand->logo_path), false)
        ->assertSee($product->name)
        ->assertSee($otherProduct->name)
        ->assertSee('.product-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr))', false)
        ->assertSee('<div class="product-meta">', false);

    $this->get(route('shop', ['brand' => $brand->slug]))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('product-brand', false);

    $this->getJson(route('shop', ['brand' => $brand->slug]))
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('products.0.name', $product->name)
        ->assertJsonPath('products.0.brand.name', $brand->name)
        ->assertJsonPath('products.0.brand.slug', $brand->slug)
        ->assertJsonPath('products.0.brand.logo', asset('storage/' . $brand->logo_path));

    $this->get(route('product.show', $product->slug))
        ->assertOk()
        ->assertSee($brand->name)
        ->assertSee(asset('storage/' . $brand->logo_path), false)
        ->assertSee(route('shop', ['brand' => $brand->slug]), false);
});

test('shop search matches product names and categories without leaking description-only matches', function () {
    $perfumeCategory = Category::create([
        'name' => 'Perfume',
        'slug' => 'perfume-search-category',
        'icon' => '✦',
        'is_active' => true,
    ]);
    $lipstickCategory = Category::create([
        'name' => 'Lipstick',
        'slug' => 'lipstick-search-category',
        'icon' => '✦',
        'is_active' => true,
    ]);
    $perfume = Product::create([
        'name' => 'Garden Perfume',
        'slug' => 'garden-perfume-search',
        'description' => 'A floral fragrance.',
        'category_id' => $perfumeCategory->id,
        'price' => 20,
        'stock' => 4,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    Product::create([
        'name' => 'Rose Lipstick',
        'slug' => 'rose-lipstick-description-match',
        'description' => 'A lipstick with notes of perfume.',
        'category_id' => $lipstickCategory->id,
        'price' => 10,
        'stock' => 4,
        'is_active' => true,
        'review_status' => 'approved',
    ]);

    $this->get(route('shop', ['search' => 'perfume']))
        ->assertOk()
        ->assertSee($perfume->name)
        ->assertDontSee('Rose Lipstick');
});
