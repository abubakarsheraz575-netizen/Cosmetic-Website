<?php

use App\Models\Setting;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin can update storefront home content and uploaded images are persisted', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=');

    $this->actingAs($admin)
        ->get('/admin/home-settings')
        ->assertOk()
        ->assertSee('Home Page Content')
        ->assertSee('name="home_hero_title"', false);

    $this->postJson('/admin/home-settings', [
        'home_hero_tagline' => 'Radiance, made personal',
        'home_hero_title' => "Find your\nnatural glow",
        'home_hero_text' => 'Thoughtful beauty for every day.',
        'home_hero_button' => 'Explore the shop',
        'home_featured_tagline' => 'Chosen for you',
        'home_featured_title' => 'Customer favourites',
        'home_featured_text' => 'A hand-picked collection.',
        'home_promo_badge' => 'New',
        'home_promo_badge_text' => 'A fresh collection',
        'home_promo_tagline' => 'Everyday rituals',
        'home_promo_title' => 'Care that feels good',
        'home_promo_text' => 'Simple routines, lovely results.',
        'home_promo_button' => 'Shop now',
        'home_feature_1_title' => 'Seasonal offers',
        'home_feature_1_text' => 'Limited-time picks.',
        'home_feature_2_title' => 'Delivery',
        'home_feature_2_text' => 'Delivered with care.',
        'home_feature_3_title' => 'Returns',
        'home_feature_3_text' => 'Easy returns.',
        'home_latest_tagline' => 'Just arrived',
        'home_latest_title' => 'New in',
        'home_latest_text' => 'Fresh additions to the store.',
        'home_show_featured' => '1',
        'home_show_promo' => '1',
        'home_show_features' => '1',
        'home_show_testimonials' => '1',
        'home_show_latest' => '1',
        'home_hero_image' => UploadedFile::fake()->createWithContent('hero.png', $png),
    ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    expect(Setting::get('home_hero_title'))->toBe("Find your\nnatural glow")
        ->and(Setting::get('home_show_featured'))->toBe('1');
    Storage::disk('public')->assertExists(Setting::get('home_hero_image'));

    $this->get('/')
        ->assertOk()
        ->assertSee('Radiance, made personal')
        ->assertSee('Find your')
        ->assertSee('natural glow')
        ->assertSee('Customer favourites')
        ->assertSee('Everyday rituals');
});

test('admin can hide home page sections and invalid image uploads are rejected', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->postJson('/admin/home-settings', [
            'home_hero_image' => UploadedFile::fake()->createWithContent('not-an-image.txt', 'not an image'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('home_hero_image');

    $this->postJson('/admin/home-settings', [
        'home_hero_tagline' => 'A changing storefront',
        'home_hero_title' => 'Discover more',
    ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    expect(Setting::get('home_show_featured'))->toBe('0')
        ->and(Setting::get('home_show_promo'))->toBe('0')
        ->and(Setting::get('home_show_testimonials'))->toBe('0');

    $this->get('/')
        ->assertOk()
        ->assertSee('A changing storefront')
        ->assertDontSee('<section class="products-sec"', false)
        ->assertDontSee('<section class="promo-sec"', false)
        ->assertDontSee('<section class="testi-sec"', false);
});

test('admin frontend sidebar links to page controls instead of public page shortcuts', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Frontend')
        ->assertSee(route('admin.home-settings'), false)
        ->assertSee(route('admin.reviews'), false)
        ->assertSee(route('admin.store-settings'), false)
        ->assertSee(route('admin.about-settings'), false)
        ->assertSee(route('admin.page-headers'), false)
        ->assertSee('Testimonials')
        ->assertSee('Contact Page');

    $response->assertDontSee('title="Open storefront home page"', false)
        ->assertDontSee('title="Open storefront shop"', false)
        ->assertDontSee('title="Open storefront about page"', false)
        ->assertDontSee('title="Open storefront testimonials"', false)
        ->assertDontSee('title="Open storefront contact page"', false);
});

test('store and about page settings update the public storefront content', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patchJson('/admin/store-settings', [
            'site_email' => 'hello@dynamic.example',
            'site_phone' => '+44 1234 567890',
            'site_address' => '42 Dynamic Street, London',
            'site_map_query' => '42 Dynamic Street, London',
            'hours_weekdays' => '8:00 AM – 5:00 PM',
            'hours_saturday' => '10:00 AM – 2:00 PM',
            'hours_sunday' => 'Closed',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->postJson('/admin/about-settings', [
        'about_tag' => 'Our changing story',
        'about_title' => 'Beauty, thoughtfully made',
        'about_text' => 'This story is managed from the admin page.',
        'about_services' => "Skin care\nDaily essentials",
        'about_product_id' => '',
    ])->assertOk()->assertJsonPath('status', 'success');

    $this->get('/contact')
        ->assertOk()
        ->assertSee('hello@dynamic.example')
        ->assertSee('+44 1234 567890')
        ->assertSee('42 Dynamic Street, London');

    $this->get('/about')
        ->assertOk()
        ->assertSee('Our changing story')
        ->assertSee('Beauty, thoughtfully made')
        ->assertSee('This story is managed from the admin page.')
        ->assertSee('Daily essentials');
});

test('home and about use the admin-managed beauty brand slider instead of category marquees', function () {
    $brand = Brand::create([
        'name' => 'Test Beauty House',
        'slug' => 'test-beauty-house',
        'is_active' => true,
    ]);
    $category = Category::create([
        'name' => 'Daily Care',
        'slug' => 'daily-care',
        'icon' => '🌿',
        'is_active' => true,
    ]);
    Product::create([
        'name' => 'Daily Cleanser',
        'slug' => 'daily-cleanser',
        'category_id' => $category->id,
        'price' => 12,
        'stock' => 5,
        'is_active' => true,
        'review_status' => 'approved',
    ]);

    $hiddenCategory = Category::create([
        'name' => 'Hidden Care',
        'slug' => 'hidden-care',
        'is_active' => false,
    ]);
    Product::create([
        'name' => 'Hidden Cleanser',
        'slug' => 'hidden-cleanser',
        'category_id' => $hiddenCategory->id,
        'price' => 12,
        'stock' => 5,
        'is_active' => false,
        'review_status' => 'approved',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('BEAUTY BRANDS')
        ->assertSee($brand->name)
        ->assertDontSee('SHOP BY CATEGORY')
        ->assertDontSee('LOGOIPSUM')
        ->assertDontSee('Hidden Care')
        ->assertDontSee('Daily Care');

    $this->get('/about')
        ->assertOk()
        ->assertSee('BEAUTY BRANDS')
        ->assertSee($brand->name)
        ->assertDontSee('Daily Care')
        ->assertDontSee(route('shop', ['category' => 'daily-care']), false);
});

test('admin can customize saved category slider logos names and visibility', function () {
    $category = Category::create([
        'name' => 'Saved category',
        'slug' => 'saved-category',
        'icon' => '🧴',
        'is_active' => true,
    ]);
    Product::create([
        'name' => 'Saved category product',
        'slug' => 'saved-category-product',
        'category_id' => $category->id,
        'price' => 8,
        'stock' => 3,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/home-settings')
        ->assertOk()
        ->assertSee('Category logo slider')
        ->assertSee('name="home_category_strip['.$category->id.'][name]"', false)
        ->assertSee('value="🧴"', false);

    $this->postJson('/admin/home-settings', [
        'home_category_strip' => [
            $category->id => [
                'name' => 'Signature care',
                'icon' => '🌼',
                'visible' => '1',
            ],
        ],
    ])->assertOk();

    $this->get('/')
        ->assertOk()
        ->assertSee('Signature care')
        ->assertSee('🌼')
        ->assertDontSee(route('shop', ['category' => 'saved-category']), false);

    $this->postJson('/admin/home-settings', [
        'home_category_strip' => [
            $category->id => [
                'name' => 'Signature care',
                'icon' => '🌼',
            ],
        ],
    ])->assertOk();

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Signature care')
        ->assertDontSee('🌼');
});

test('admin can upload safe svg category logos used by the home and shop category lists', function () {
    Storage::fake('public');
    $category = Category::create([
        'name' => 'Botanical care',
        'slug' => 'botanical-care',
        'icon' => '🌿',
        'is_active' => true,
    ]);
    Product::create([
        'name' => 'Botanical serum',
        'slug' => 'botanical-serum',
        'category_id' => $category->id,
        'price' => 12,
        'stock' => 3,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    $admin = User::factory()->create(['role' => 'admin']);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><script>alert(1)</script><path d="M1 1h14v14H1z" fill="currentColor"/></svg>';

    $this->actingAs($admin)->post('/admin/home-settings', [
        'home_category_strip' => [
            $category->id => [
                'name' => 'Botanical essentials',
                'icon' => 'ri-leaf-line',
                'visible' => '1',
                'logo' => UploadedFile::fake()->createWithContent('leaf.svg', $svg),
            ],
        ],
    ], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $stored = json_decode((string) Setting::get('home_category_strip'), true);
    $logoPath = $stored[(string) $category->id]['logo'];
    Storage::disk('public')->assertExists($logoPath);
    $safeSvg = Storage::disk('public')->get($logoPath);
    expect($safeSvg)->not->toContain('<script');
    expect($safeSvg)->toContain('<path');

    $logoUrl = asset('storage/' . $logoPath);
    $this->get('/')->assertOk()->assertSee($logoUrl, false)->assertSee('Botanical essentials');
    $this->get('/shop')->assertOk()->assertSee($logoUrl, false)->assertSee('Botanical care');
    $this->get('/admin/about-settings')
        ->assertOk()
        ->assertSee('data-category-class="ri-leaf-line"', false)
        ->assertSee('data-category-logo="'.$logoUrl.'"', false);
});

test('admin can configure homepage slider brands and the frontend follows saved visibility and order', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=');

    $this->actingAs($admin)
        ->get('/admin/home-slider')
        ->assertOk()
        ->assertSee('Homepage Slider')
        ->assertSee("L'Oréal Paris")
        ->assertSee(route('admin.home-slider'), false)
        ->assertSee('name="order[]"', false);

    $this->put('/admin/home-slider', [
        'order' => ['custom-glow', 'featured-loreal-paris', 'featured-maybelline-new-york'],
        'brands' => [
            'featured-loreal-paris' => [
                'name' => "L'Oréal Paris",
                'mark' => 'LP',
                'visible' => '1',
            ],
            'featured-maybelline-new-york' => [
                'name' => 'Maybelline New York',
                'mark' => 'M',
            ],
            'custom-glow' => [
                'name' => 'Glow Atelier',
                'mark' => 'GA',
                'visible' => '1',
                'logo' => UploadedFile::fake()->createWithContent('glow.png', $png),
            ],
        ],
    ])->assertRedirect('/admin/home-slider');

    $saved = json_decode((string) Setting::get('home_brand_slider'), true, 512, JSON_THROW_ON_ERROR);
    expect(array_column($saved, 'name'))->toBe(['Glow Atelier', "L'Oréal Paris", 'Maybelline New York']);
    expect($saved[0]['logo_owned'])->toBeTrue();
    Storage::disk('public')->assertExists($saved[0]['logo_path']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Glow Atelier')
        ->assertSee("L'Oréal Paris")
        ->assertDontSee('Maybelline New York')
        ->assertSee(asset('storage/' . $saved[0]['logo_path']), false);
});
