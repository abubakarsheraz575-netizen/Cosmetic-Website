<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function mediaManagementPng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pKcAAAAASUVORK5CYII=');
}

test('admins can browse media folders and see the storage location of images', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/moisturizer.jpg', 'image-data');
    Storage::disk('public')->put('products/ingredients/label.png', 'image-data');

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('admin.media.index'))
        ->assertOk()
        ->assertSee('products')
        ->assertSee('storage/app/public/(root)');
    $this->get(route('admin.media.index', ['folder' => 'products']))
        ->assertOk()
        ->assertSee('moisturizer.jpg')
        ->assertSee('storage/app/public/products/moisturizer.jpg')
        ->assertSee('ingredients');
    $this->get(route('admin.media.index', ['folder' => '../private']))->assertNotFound();
});

test('admins can create media category folders', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.media.folders.store'), ['name' => 'Campaign Photos'])
        ->assertRedirect(route('admin.media.index'))
        ->assertSessionHas('success');

    expect(Storage::disk('public')->directoryExists('campaign-photos'))->toBeTrue();
});

test('admins can upload local media and search it from the picker', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.media.upload'), [
        'images' => [UploadedFile::fake()->createWithContent('campaign.png', mediaManagementPng())],
    ])->assertRedirect(route('admin.media.index', ['folder' => 'media']))
        ->assertSessionHas('success');

    $path = collect(Storage::disk('public')->allFiles('media'))->sole();
    $this->getJson(route('admin.media.picker'))
        ->assertOk()
        ->assertJsonPath('images.0.path', $path);
});

test('admins cannot delete an image used by a product but can delete an unused image', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Media Test Category',
        'slug' => 'media-test-category',
        'icon' => '🧴',
        'is_active' => true,
    ]);
    $product = Product::create([
        'name' => 'Media Test Product',
        'slug' => 'media-test-product',
        'description' => 'Product used to verify media references.',
        'category_id' => $category->id,
        'price' => 10,
        'discount' => 0,
        'stock' => 1,
        'emoji' => '🧴',
        'is_active' => true,
    ]);
    Storage::disk('public')->put('products/used.jpg', 'image');
    Storage::disk('public')->put('media/unused.jpg', 'image');
    ProductImage::create(['product_id' => $product->id, 'path' => 'products/used.jpg']);

    $this->actingAs($admin)->post(route('admin.media.delete'), ['path' => 'products/used.jpg'])
        ->assertSessionHasErrors('path');
    MediaLibrary::deleteIfUnused('products/used.jpg');
    expect(Storage::disk('public')->exists('products/used.jpg'))->toBeTrue();

    $this->post(route('admin.media.delete'), ['path' => 'media/unused.jpg'])
        ->assertRedirect(route('admin.media.index'))
        ->assertSessionHas('success');
    expect(Storage::disk('public')->exists('media/unused.jpg'))->toBeFalse();
});
