<?php

use App\Http\Controllers\Admin\CustomerDirectoryController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    if (! Route::has('admin.ecommerce.customers.index')) {
        Route::get('/admin/ecommerce/customers', [CustomerDirectoryController::class, 'index'])
            ->middleware(['web', 'auth', 'is_admin'])
            ->name('admin.ecommerce.customers.index');
        Route::get('/admin/ecommerce/customers/{user}', [CustomerDirectoryController::class, 'show'])
            ->middleware(['web', 'auth', 'is_admin'])
            ->name('admin.ecommerce.customers.show');
        Route::getRoutes()->refreshNameLookups();
    }
});

function customerDirectoryProduct(User $submitter, string $name, string $reviewStatus = 'pending', bool $active = false): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'customer-directory-category'],
        ['name' => 'Directory Category', 'icon' => '✦', 'description' => '', 'is_active' => true]
    );

    return Product::create([
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
        'description' => 'Vendor submitted product',
        'category_id' => $category->id,
        'price' => 24.50,
        'discount' => 0,
        'stock' => 7,
        'emoji' => '✦',
        'is_active' => $active,
        'submitted_by' => $submitter->id,
        'review_status' => $reviewStatus,
    ]);
}

test('admin customer directory searches and paginates with vendor status derived from submitted products', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = User::factory()->create([
        'role' => 'user',
        'name' => 'Avery Vendor',
        'email' => 'avery@example.test',
        'profile_photo' => 'profiles/avery.jpg',
    ]);
    $roleOnlyVendor = User::factory()->create([
        'role' => 'vendor',
        'name' => 'Role Only',
        'email' => 'role-only@example.test',
    ]);
    customerDirectoryProduct($vendor, 'Avery Serum');
    for ($i = 0; $i < 12; $i++) {
        User::factory()->create(['name' => 'Directory Person ' . $i]);
    }

    $this->actingAs($admin)->get(route('admin.ecommerce.customers.index', ['q' => 'avery']))
        ->assertOk()
        ->assertSee('Avery Vendor')
        ->assertSee('avery@example.test')
        ->assertSee('Vendor')
        ->assertDontSee('Role Only')
        ->assertSee('View profile');

    $this->actingAs($admin)->get(route('admin.ecommerce.customers.index', ['type' => 'customer', 'status' => 'active', 'per_page' => 10]))
        ->assertOk()
        ->assertSee('Directory Person 11')
        ->assertDontSee('Avery Vendor')
        ->assertDontSee('Role Only');

    $this->actingAs($admin)->get(route('admin.ecommerce.customers.index', ['type' => 'vendor']))
        ->assertOk()
        ->assertSee('Avery Vendor')
        ->assertDontSee('Role Only');
});

test('admin vendor profile shows available contact data and all submitted product states and inventory', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = User::factory()->create([
        'name' => 'Morgan Vendor',
        'email' => 'morgan@example.test',
        'phone' => '+1 555 0100',
        'profile_photo' => 'profiles/morgan.jpg',
        'skin_type' => 'Combination',
        'hair_type' => 'Wavy',
        'shade_match' => 'Warm beige',
        'loves' => 'Botanical skincare',
        'email_verified_at' => now(),
    ]);
    $published = customerDirectoryProduct($vendor, 'Published Cleanser', 'approved', true);
    $pending = customerDirectoryProduct($vendor, 'Pending Lotion');
    customerDirectoryProduct($vendor, 'Rejected Scrub', 'rejected');
    ProductImage::create(['product_id' => $published->id, 'path' => 'products/cleanser.jpg', 'sort_order' => 0]);
    ProductImage::create(['product_id' => $published->id, 'path' => 'products/cleanser-back.jpg', 'sort_order' => 1]);
    ProductImage::create(['product_id' => $pending->id, 'path' => 'products/lotion.jpg', 'sort_order' => 0]);

    $this->actingAs($admin)->get(route('admin.ecommerce.customers.show', ['user' => $vendor->id]))
        ->assertOk()
        ->assertSee('Morgan Vendor')
        ->assertSee('morgan@example.test')
        ->assertSee('+1 555 0100')
        ->assertSee('Combination')
        ->assertSee('Wavy')
        ->assertSee('Warm beige')
        ->assertSee('Botanical skincare')
        ->assertSee('Email verified')
        ->assertSee('3') // submitted product count
        ->assertSee('1') // published product count
        ->assertSee('Published Cleanser')
        ->assertSee('Pending Lotion')
        ->assertSee('Rejected Scrub')
        ->assertSee('Published')
        ->assertSee('Pending')
        ->assertSee('Rejected')
        ->assertSee('7') // stock
        ->assertSee('2'); // published product image count
});

test('customers and administrators cannot be opened as vendors without submitted products', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $adminWithProduct = User::factory()->create(['role' => 'admin']);
    customerDirectoryProduct($adminWithProduct, 'Admin submission');

    $this->actingAs($admin)->get(route('admin.ecommerce.customers.show', ['user' => $customer->id]))->assertNotFound();
    $this->actingAs($admin)->get(route('admin.ecommerce.customers.show', ['user' => $adminWithProduct->id]))->assertNotFound();
});

test('customer directory validates page sizes and preserves search filters and sorting in pagination', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    foreach (range(1, 12) as $index) {
        User::factory()->create([
            'name' => 'Page Person ' . $index,
            'email' => 'page-person-' . $index . '@example.test',
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.ecommerce.customers.index', [
            'q' => 'Page Person',
            'type' => 'customer',
            'status' => 'active',
            'per_page' => 10,
            'sort' => 'name',
            'direction' => 'asc',
        ]))
        ->assertOk()
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('type="search" name="q"', false)
        ->assertSee('name="per_page"', false)
        ->assertSee('10 per page')
        ->assertSee('25 per page')
        ->assertSee('50 per page')
        ->assertSee('Page Person 1')
        ->assertSee('q=Page%20Person', false)
        ->assertSee('type=customer', false)
        ->assertSee('status=active', false)
        ->assertSee('per_page=10', false)
        ->assertSee('sort=name', false)
        ->assertSee('direction=asc', false);

    $this->actingAs($admin)
        ->get(route('admin.ecommerce.customers.index', ['per_page' => 20]))
        ->assertSessionHasErrors('per_page');
});
