<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function brandTestPng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pKcAAAAASUVORK5CYII=');
}

function brandTestProduct(string $name = 'Brand Test Product', array $attributes = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'brand-test-category'],
        ['name' => 'Brand Test Category', 'icon' => '✦', 'is_active' => true]
    );

    return Product::create(array_merge([
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
        'description' => 'Product for brand tests',
        'category_id' => $category->id,
        'price' => 24.50,
        'discount' => 0,
        'stock' => 13,
        'emoji' => '✦',
        'is_active' => true,
    ], $attributes));
}

test('brand product relation reflects assigned products and brand deletion preserves products', function () {
    $brand = Brand::create([
        'name' => 'Relation Brand',
        'slug' => 'relation-brand',
        'is_active' => true,
    ]);
    $product = brandTestProduct();
    $brand->products()->save($product);

    expect($brand->products()->pluck('products.id')->all())->toBe([$product->id]);

    $brand->delete();
    expect($product->fresh()->brand_id)->toBeNull();
});

test('admins can choose brand logos from the media library', function () {
    Storage::fake('public');
    Storage::disk('public')->put('media/brand-logo.png', brandTestPng());
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.ecommerce.brands.store'), [
        'name' => 'Media Library Brand',
        'logo_path' => 'media/brand-logo.png',
        'is_active' => '1',
    ])->assertRedirect(route('admin.ecommerce.brands'));

    expect(Brand::where('name', 'Media Library Brand')->value('logo_path'))
        ->toBe('media/brand-logo.png');
    Storage::disk('public')->assertExists('media/brand-logo.png');
});

test('admins can browse brands and see product images and stock quantities', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $brand = Brand::create([
        'name' => 'Glow House',
        'slug' => 'glow-house',
        'description' => 'Thoughtful skincare',
        'logo_path' => UploadedFile::fake()->createWithContent('logo.png', brandTestPng())->store('brands', 'public'),
        'is_active' => true,
    ]);
    $product = brandTestProduct('Glow Serum', ['stock' => 29]);
    $brand->products()->save($product);
    $path = UploadedFile::fake()->createWithContent('serum.png', brandTestPng())->store('products', 'public');
    ProductImage::create(['product_id' => $product->id, 'path' => $path, 'sort_order' => 0]);

    $this->actingAs($admin)->get(route('admin.ecommerce.brands'))
        ->assertOk()
        ->assertSee('Glow House')
        ->assertSee('1');
    $brandIndex = $this->actingAs($admin)->get(route('admin.ecommerce.brands', [
        'q' => 'Glow',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('type="search" name="q"', false)
        ->assertSee('name="per_page"', false)
        ->assertSee('10 per page')
        ->assertSee('25 per page')
        ->assertSee('50 per page');
    $brandProducts = $this->actingAs($admin)->get(route('admin.ecommerce.brands.show', [
        'brand' => $brand,
        'q' => 'Glow',
        'per_page' => 10,
    ]))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('type="search" name="q"', false)
        ->assertSee('name="per_page"', false)
        ->assertSee('10 per page')
        ->assertSee('25 per page')
        ->assertSee('50 per page')
        ->assertSee('Glow Serum')
        ->assertSee('£24.50')
        ->assertSee('29')
        ->assertSee('storage/'.$path, false);
});

test('admins can create brands with validated logos and assign products', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $product = brandTestProduct('New Brand Serum');

    $this->actingAs($admin)->post(route('admin.ecommerce.brands.store'), [
        'name' => 'Luminous Labs',
        'description' => 'A descriptive brand profile.',
        'website_url' => 'https://example.test',
        'is_active' => '1',
        'logo' => UploadedFile::fake()->createWithContent('logo.png', brandTestPng()),
        'products' => [$product->id],
    ])->assertRedirect(route('admin.ecommerce.brands'));

    $brand = Brand::where('name', 'Luminous Labs')->firstOrFail();
    expect($brand->slug)->toBe('luminous-labs')
        ->and($brand->products()->pluck('products.id')->all())->toBe([$product->id]);
    Storage::disk('public')->assertExists($brand->logo_path);
});

test('admins can update assignments and replace or remove brand images', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $oldLogo = UploadedFile::fake()->createWithContent('old.png', brandTestPng())->store('brands', 'public');
    $brand = Brand::create(['name' => 'Existing Brand', 'slug' => 'existing-brand', 'logo_path' => $oldLogo]);
    $first = brandTestProduct('First Brand Product');
    $brand->products()->save($first);
    $second = brandTestProduct('Second Brand Product');

    $this->actingAs($admin)->put(route('admin.ecommerce.brands.update', $brand), [
        'name' => 'Existing Brand Renamed',
        'is_active' => '1',
        'remove_logo' => '1',
        'products' => [$second->id],
    ])->assertRedirect(route('admin.ecommerce.brands'));

    expect($first->fresh()->brand_id)->toBeNull()
        ->and($second->fresh()->brand_id)->toBe($brand->id)
        ->and($brand->fresh()->logo_path)->toBeNull()
        ->and($brand->fresh()->slug)->toBe('existing-brand');
    Storage::disk('public')->assertMissing($oldLogo);
});

test('brand uploads and product assignments reject invalid data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.ecommerce.brands.store'), [
        'name' => 'Bad Upload',
        'logo' => UploadedFile::fake()->create('document.pdf', 50, 'application/pdf'),
        'products' => [99999],
    ])->assertSessionHasErrors(['logo', 'products.0']);

    expect(Brand::where('name', 'Bad Upload')->exists())->toBeFalse();
});

test('brand indexes validate page size and preserve search and sort across pagination', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    foreach (range(1, 12) as $index) {
        Brand::create([
            'name' => 'PageBrand ' . $index,
            'slug' => 'page-brand-' . $index,
            'is_active' => true,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.ecommerce.brands', [
            'q' => 'PageBrand',
            'per_page' => 10,
            'sort' => 'name',
            'direction' => 'desc',
        ]))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('10 per page')
        ->assertSee('PageBrand 9')
        ->assertSee('q=PageBrand', false)
        ->assertSee('per_page=10', false)
        ->assertSee('sort=name', false)
        ->assertSee('direction=desc', false)
        ->assertSee('class="brand-page active" aria-current="page">1</span>', false)
        ->assertSee('>2</a>', false);

    $this->actingAs($admin)
        ->get(route('admin.ecommerce.brands', ['per_page' => 20]))
        ->assertSessionHasErrors('per_page');
});

test('brand product table validates its page size and preserves search and sort in pagination links', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $brand = Brand::create([
        'name' => 'Pagination Brand',
        'slug' => 'pagination-brand',
        'is_active' => true,
    ]);
    foreach (range(1, 12) as $index) {
        $brand->products()->save(brandTestProduct('Pagination Serum ' . $index));
    }

    $this->actingAs($admin)
        ->get(route('admin.ecommerce.brands.show', [
            'brand' => $brand,
            'q' => 'Pagination Serum',
            'per_page' => 10,
            'sort' => 'name',
            'direction' => 'desc',
        ]))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('q=Pagination%20Serum', false)
        ->assertSee('per_page=10', false)
        ->assertSee('sort=name', false)
        ->assertSee('direction=desc', false)
        ->assertSee('page=2', false)
        ->assertSee('class="brand-page active" aria-current="page">1</span>', false)
        ->assertSee('>2</a>', false)
        ->assertSee('brand-pagination-links', false);

    $this->actingAs($admin)
        ->get(route('admin.ecommerce.brands.show', ['brand' => $brand, 'per_page' => 20]))
        ->assertSessionHasErrors('per_page');
});
