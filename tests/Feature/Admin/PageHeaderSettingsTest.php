<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin can customize banner titles and images for storefront pages', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Header Test',
        'slug' => 'header-test',
    ]);
    Product::create([
        'name' => 'Header Test Product',
        'slug' => 'header-test-product',
        'category_id' => $category->id,
        'price' => 10,
        'stock' => 5,
        'is_active' => true,
        'review_status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get('/admin/page-headers')
        ->assertOk()
        ->assertSee('Storefront Page Headers')
        ->assertSee('name="page_header_product_detail_title"', false);

    $this->patchJson('/admin/page-headers', [
        'page_header_shop_title' => 'Explore the collection',
        'page_header_testimonials_title' => 'Real customer stories',
        'page_header_about_title' => 'Our story',
        'page_header_contact_title' => 'Talk to our team',
        'page_header_product_detail_title' => 'Product spotlight',
        'page_header_blog_title' => 'Beauty journal stories',
        'page_header_blog_description' => 'Your new editable journal introduction.',
        'page_header_faqs_title' => 'Your questions answered',
        'page_header_shop_image' => UploadedFile::fake()->createWithContent(
            'shop-banner.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=')
        ),
    ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $savedImage = Setting::get('page_header_shop_image');
    Storage::disk('public')->assertExists($savedImage);

    $this->get('/shop')
        ->assertOk()
        ->assertSee('Explore the collection')
        ->assertSee(asset('storage/' . $savedImage), false);

    $this->get('/testimonials')->assertOk()->assertSee('Real customer stories');
    $this->get('/about')->assertOk()->assertSee('Our story');
    $this->get('/contact')->assertOk()->assertSee('Talk to our team');
    $this->get('/blog')->assertOk()->assertSee('Beauty journal stories')
        ->assertSee('Your new editable journal introduction.');
    $this->get('/faqs')->assertOk()->assertSee('Your questions answered');
    $this->get('/product/header-test-product')->assertOk()->assertSee('Product spotlight')
        ->assertSee('Header Test Product');
});

test('page header settings reject invalid images and missing page titles', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patchJson('/admin/page-headers', [
            'page_header_shop_title' => '',
            'page_header_testimonials_title' => 'Testimonials',
            'page_header_about_title' => 'About',
            'page_header_contact_title' => 'Contact',
            'page_header_product_detail_title' => 'Product Details',
            'page_header_blog_title' => 'Beauty Journal',
            'page_header_blog_description' => 'A journal introduction.',
            'page_header_faqs_title' => 'FAQs',
            'page_header_shop_image' => UploadedFile::fake()->create('banner.txt', 20, 'text/plain'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['page_header_shop_title', 'page_header_shop_image']);
});
