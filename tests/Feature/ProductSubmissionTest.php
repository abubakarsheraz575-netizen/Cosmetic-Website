<?php

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\SpecificationAttribute;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

function submissionCategory(): Category
{
    return Category::create([
        'name' => 'Submission Test Category',
        'slug' => 'submission-test-category',
        'icon' => '🧴',
        'is_active' => true,
    ]);
}

function submissionPayload(Category $category): array
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pS8AAAAASUVORK5CYII=');

    return [
        'name' => 'Customer Submitted Serum',
        'category_id' => $category->id,
        'price' => '18.50',
        'discount' => '5',
        'stock' => '8',
        'emoji' => '🧴',
        'description' => 'A customer-provided product description.',
        'images' => [UploadedFile::fake()->createWithContent('serum.png', $png)],
        'cover_image_index' => 0,
    ];
}

test('customer submissions stay hidden until approved and become shop and cart eligible', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $payload = submissionPayload(submissionCategory());

    $this->actingAs($customer)
        ->post(route('user.product-submissions.store'), $payload)
        ->assertRedirect('/user/dashboard/products');
    Mail::assertSent(AdminActivityNotification::class, function (AdminActivityNotification $mail) use ($customer) {
        return $mail->activityTitle === 'Product submitted for review'
            && $mail->hasTo(config('mail.from.address'))
            && str_contains($mail->details['Submitted by'], $customer->email);
    });

    $product = Product::where('name', 'Customer Submitted Serum')->firstOrFail();
    expect($product->review_status)->toBe('pending')
        ->and($product->is_active)->toBeFalse()
        ->and($product->submitted_by)->toBe($customer->id);
    expect($product->images->first()->is_cover)->toBeTrue();
    Storage::disk('public')->assertExists($product->images->first()->path);

    $this->get(route('user.product-submissions.index'))->assertOk()
        ->assertDontSee('Your submissions')
        ->assertSee('Submit a product');
    $this->get(route('user.product-submissions.submissions'))->assertOk()
        ->assertSee($product->name)
        ->assertSee('pending');
    $this->get('/shop')->assertDontSee($product->name);
    $this->get('/product/' . $product->slug)->assertNotFound();
    $this->postJson('/cart/add', ['product_id' => $product->id])->assertNotFound();

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
        ->get(route('admin.product-submissions.index'))
        ->assertOk()
        ->assertSee($product->name);
    $this->actingAs($admin)
        ->get(route('admin.product-submissions.show', $product))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('A customer-provided product description.');

    $this->post(route('admin.product-submissions.approve', $product))
        ->assertRedirect('/admin/product-submissions');

    $product->refresh();
    expect($product->review_status)->toBe('approved')
        ->and($product->is_active)->toBeTrue()
        ->and($product->reviewed_by)->toBe($admin->id)
        ->and($product->reviewed_at)->not->toBeNull();

    $this->get(route('admin.product-submissions.index'))
        ->assertSee($product->name)
        ->assertSee('approved');
    $this->get(route('admin.product-submissions.show', $product))
        ->assertOk()
        ->assertDontSee('Approve and publish')
        ->assertSee('Reviewed');

    $this->get('/shop')->assertSee($product->name);
    $this->get('/product/' . $product->slug)->assertOk()->assertSee($product->name);
    $this->postJson('/cart/add', ['product_id' => $product->id])->assertOk()
        ->assertJsonPath('status', 'success');
});

test('admin rejection leaves a customer submission unavailable', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->post(route('user.product-submissions.store'), submissionPayload(submissionCategory()));
    $product = Product::where('name', 'Customer Submitted Serum')->firstOrFail();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.product-submissions.reject', $product))
        ->assertRedirect('/admin/product-submissions');

    $product->refresh();
    expect($product->review_status)->toBe('rejected')
        ->and($product->is_active)->toBeFalse()
        ->and($product->reviewed_by)->toBe($admin->id);

    $this->get(route('admin.product-submissions.index'))
        ->assertSee($product->name)
        ->assertSee('rejected');
    $this->get('/shop')->assertDontSee($product->name);
    $this->get('/product/' . $product->slug)->assertNotFound();
    $this->postJson('/cart/add', ['product_id' => $product->id])->assertNotFound();
});

test('submission validates product details and image requirements', function () {
    $customer = User::factory()->create();
    $category = submissionCategory();

    $this->actingAs($customer)
        ->from(route('user.product-submissions.index'))
        ->post(route('user.product-submissions.store'), [
            'name' => '',
            'category_id' => $category->id,
            'price' => '-1',
            'stock' => '-2',
            'images' => [UploadedFile::fake()->create('notes.txt', 2, 'text/plain')],
        ])
        ->assertRedirect(route('user.product-submissions.index'))
        ->assertSessionHasErrors(['name', 'price', 'stock', 'images.0']);

    expect(Product::count())->toBe(0);
});

test('customer product form uses category specifications and saves brand and package details for admin review', function () {
    Mail::fake();
    $customer = User::factory()->create();
    $category = submissionCategory();
    $skinType = SpecificationAttribute::create([
        'category_id' => $category->id,
        'name' => 'Skin type',
        'type' => 'select',
        'options' => ['Dry', 'Oily', 'All skin types'],
        'is_required' => true,
    ]);
    $brand = Brand::create([
        'name' => 'Test Beauty',
        'slug' => 'test-beauty',
        'is_active' => true,
    ]);

    $this->actingAs($customer)
        ->get(route('user.product-submissions.index'))
        ->assertOk()
        ->assertSee('Brand')
        ->assertSee('Shipping package')
        ->assertSee('Skin type')
        ->assertSee('All skin types');

    $missingRequiredValue = submissionPayload($category);
    $this->from(route('user.product-submissions.index'))
        ->post(route('user.product-submissions.store'), $missingRequiredValue)
        ->assertRedirect(route('user.product-submissions.index'))
        ->assertSessionHasErrors('specifications.'.$skinType->id);
    expect(Product::count())->toBe(0);

    $payload = submissionPayload($category);
    $payload['brand_id'] = $brand->id;
    $payload['specifications'] = [$skinType->id => 'All skin types'];
    $payload['shipping_weight'] = '250';
    $payload['weight_unit'] = 'g';
    $payload['shipping_length'] = '12';
    $payload['shipping_width'] = '5';
    $payload['shipping_height'] = '4';
    $payload['dimension_unit'] = 'cm';

    $this->post(route('user.product-submissions.store'), $payload)
        ->assertRedirect('/user/dashboard/products');

    $product = Product::where('name', 'Customer Submitted Serum')->firstOrFail();
    expect($product->brand_id)->toBe($brand->id)
        ->and((float) $product->shipping_weight)->toBe(250.0)
        ->and($product->weight_unit)->toBe('g')
        ->and((float) $product->shipping_length)->toBe(12.0)
        ->and((float) $product->shipping_width)->toBe(5.0)
        ->and((float) $product->shipping_height)->toBe(4.0)
        ->and($product->dimension_unit)->toBe('cm')
        ->and($product->review_status)->toBe('pending')
        ->and($product->is_active)->toBeFalse();
    expect($product->specificationValues()->where('specification_attribute_id', $skinType->id)->value('value'))
        ->toBe('All skin types');
});

test('customer submissions cannot assign specification fields from a different category', function () {
    $customer = User::factory()->create();
    $category = submissionCategory();
    $otherCategory = Category::create([
        'name' => 'Other Submission Category',
        'slug' => 'other-submission-category',
        'is_active' => true,
    ]);
    $foreignAttribute = SpecificationAttribute::create([
        'category_id' => $otherCategory->id,
        'name' => 'Scent',
        'type' => 'text',
    ]);
    $payload = submissionPayload($category);
    $payload['specifications'] = [$foreignAttribute->id => 'Floral'];

    $this->actingAs($customer)
        ->from(route('user.product-submissions.index'))
        ->post(route('user.product-submissions.store'), $payload)
        ->assertRedirect(route('user.product-submissions.index'))
        ->assertSessionHasErrors('specifications');

    expect(Product::count())->toBe(0);
});

test('admin-created products retain the existing approved catalog behavior', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = submissionCategory();

    $this->actingAs($admin)
        ->postJson('/admin/products', [
            'name' => 'Admin Catalog Product',
            'category_id' => $category->id,
            'price' => '25.00',
            'stock' => '4',
            'emoji' => '🧴',
            'is_active' => true,
        ])
        ->assertOk();

    $product = Product::where('name', 'Admin Catalog Product')->firstOrFail();
    expect($product->review_status)->toBe('approved')
        ->and($product->is_active)->toBeTrue()
        ->and($product->submitted_by)->toBeNull();
});

test('customer product submission and admin decision support AJAX responses', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $response = $this->actingAs($customer)
        ->withHeader('Accept', 'application/json')
        ->post(route('user.product-submissions.store'), submissionPayload(submissionCategory()))
        ->assertCreated()
        ->assertJsonPath('submission.review_status', 'pending')
        ->assertJsonPath('redirect', route('user.product-submissions.submissions'));

    $product = Product::where('name', 'Customer Submitted Serum')->firstOrFail();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->postJson(route('admin.product-submissions.approve', $product))
        ->assertOk()
        ->assertJsonPath('message', 'Customer Submitted Serum approved and published.');

    expect($product->fresh()->review_status)->toBe('approved');
    expect($response->json('message'))->toContain('submitted for admin review');
});

test('customer submission list is private searchable filterable and paginated', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $otherCustomer = User::factory()->create(['role' => 'user']);
    $category = submissionCategory();

    foreach (range(1, 12) as $number) {
        Product::create([
            'name' => 'My Submission ' . $number,
            'slug' => 'my-submission-' . $number,
            'category_id' => $category->id,
            'price' => 10,
            'stock' => 2,
            'is_active' => false,
            'submitted_by' => $customer->id,
            'review_status' => $number === 1 ? 'approved' : 'pending',
        ]);
    }

    Product::create([
        'name' => 'Another Customer Product',
        'slug' => 'another-customer-product',
        'category_id' => $category->id,
        'price' => 10,
        'stock' => 2,
        'is_active' => false,
        'submitted_by' => $otherCustomer->id,
        'review_status' => 'pending',
    ]);

    $this->actingAs($customer)
        ->get(route('user.product-submissions.submissions'))
        ->assertOk()
        ->assertSee('My Submission 12')
        ->assertDontSee('My Submission 1</strong>')
        ->assertDontSee('Another Customer Product')
        ->assertSee('page=2');

    $this->get(route('user.product-submissions.submissions', ['q' => 'Submission 1', 'status' => 'approved']))
        ->assertOk()
        ->assertSee('My Submission 1')
        ->assertDontSee('My Submission 2');

    $this->get(route('user.product-submissions.submissions', ['status' => 'rejected']))
        ->assertOk()
        ->assertSee('No matching submissions');
});

test('admin submission pagination stays server-driven and product catalog pagination stays page-owned', function () {
    $vendor = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    $category = submissionCategory();

    foreach (range(1, 21) as $number) {
        Product::create([
            'name' => 'Pagination Vendor Listing '.$number,
            'slug' => 'pagination-vendor-listing-'.$number,
            'category_id' => $category->id,
            'price' => 10,
            'stock' => 2,
            'is_active' => false,
            'submitted_by' => $vendor->id,
            'review_status' => 'pending',
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.product-submissions.index', ['search' => 'Pagination Vendor Listing']))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('page=2')
        ->assertSee('Pagination Vendor Listing 1')
        ->assertSee('Showing 1–20 of 21 submissions')
        ->assertDontSee('Search records...');

    $this->get(route('admin.product-submissions.index', ['search' => 'Pagination Vendor Listing', 'page' => 2]))
        ->assertOk()
        ->assertSee('Pagination Vendor Listing')
        ->assertSee('data-server-pagination="true"', false);

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('id="pgn"', false)
        ->assertSee('id="tblInfo"', false)
        ->assertDontSee('Search records...');
});
