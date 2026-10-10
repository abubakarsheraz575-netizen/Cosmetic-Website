<?php

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Size;
use App\Models\User;

function announcementTestProduct(?Category $category = null): Product
{
    static $counter = 0;
    $index = ++$counter;
    $category ??= Category::create([
        'name' => "Announcement Test Category {$index}",
        'slug' => "announcement-test-category-{$index}",
    ]);
    $size = Size::create(['name' => "30 ml {$index}", 'ml' => 30]);

    return Product::create([
        'name' => "Announcement Test Product {$index}",
        'slug' => "announcement-test-product-{$index}",
        'category_id' => $category->id,
        'size_id' => $size->id,
        'price' => 10,
        'stock' => 5,
    ]);
}

function announcementTestOrder(?User $user): Order
{
    static $counter = 0;
    $counter++;

    return Order::create([
        'user_id' => $user?->id,
        'order_number' => 'ANNOUNCEMENT-TEST-'.$counter,
        'name' => $user?->name ?? 'Guest Customer',
        'email' => $user?->email ?? 'guest@example.test',
        'address' => '1 Test Road',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'UK',
        'subtotal' => 10,
        'shipping' => 0,
        'total' => 10,
        'payment_method' => 'test',
        'payment_status' => 'paid',
        'status' => 'processing',
    ]);
}

test('category announcements target distinct customers with matching order items', function () {
    $purchased = announcementTestProduct();
    $alsoPurchased = announcementTestProduct();
    $unrelated = announcementTestProduct();
    $firstCustomer = User::factory()->create();
    $secondCustomer = User::factory()->create();
    $unrelatedCustomer = User::factory()->create();

    $firstOrder = announcementTestOrder($firstCustomer);
    OrderItem::create([
        'order_id' => $firstOrder->id,
        'product_id' => $purchased->id,
        'product_name' => $purchased->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $repeatOrder = announcementTestOrder($firstCustomer);
    foreach ([$purchased, $alsoPurchased] as $product) {
        OrderItem::create([
            'order_id' => $repeatOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 10,
            'qty' => 1,
            'line_total' => 10,
        ]);
    }
    $secondOrder = announcementTestOrder($secondCustomer);
    OrderItem::create([
        'order_id' => $secondOrder->id,
        'product_id' => $alsoPurchased->id,
        'product_name' => $alsoPurchased->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $unrelatedOrder = announcementTestOrder($unrelatedCustomer);
    OrderItem::create([
        'order_id' => $unrelatedOrder->id,
        'product_id' => $unrelated->id,
        'product_name' => $unrelated->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $guestOrder = announcementTestOrder(null);
    OrderItem::create([
        'order_id' => $guestOrder->id,
        'product_id' => $purchased->id,
        'product_name' => $purchased->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $unpaidCustomer = User::factory()->create();
    $unpaidOrder = announcementTestOrder($unpaidCustomer);
    $unpaidOrder->update(['payment_status' => 'pending']);
    OrderItem::create([
        'order_id' => $unpaidOrder->id,
        'product_id' => $purchased->id,
        'product_name' => $purchased->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $cancelledCustomer = User::factory()->create();
    $cancelledOrder = announcementTestOrder($cancelledCustomer);
    $cancelledOrder->update(['status' => 'cancelled']);
    OrderItem::create([
        'order_id' => $cancelledOrder->id,
        'product_id' => $purchased->id,
        'product_name' => $purchased->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);

    $announcement = Announcement::create([
        'title' => 'Product news',
        'message' => 'An update for the selected products.',
        'type' => 'info',
        'is_active' => true,
    ]);
    $announcement->categories()->sync([$purchased->category_id, $alsoPurchased->category_id]);

    $paused = Announcement::create([
        'title' => 'Paused news',
        'message' => 'This should not appear.',
        'type' => 'info',
        'is_active' => false,
    ]);
    $paused->categories()->attach($purchased->category_id);

    expect(Announcement::forCustomer($firstCustomer->id)->pluck('id')->all())->toBe([$announcement->id])
        ->and(Announcement::forCustomer($secondCustomer->id)->pluck('id')->all())->toBe([$announcement->id])
        ->and(Announcement::forCustomer($unrelatedCustomer->id)->pluck('id')->all())->toBe([])
        ->and(Announcement::forCustomer($unpaidCustomer->id)->pluck('id')->all())->toBe([])
        ->and(Announcement::forCustomer($cancelledCustomer->id)->pluck('id')->all())->toBe([])
        ->and(Announcement::forCustomer($guestOrder->user_id ?? 0)->pluck('id')->all())->toBe([]);
});

test('admin shipping page contains delivery settings without transit or global notice panels', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/shipping')
        ->assertOk()
        ->assertSee('Shipping settings')
        ->assertSee('Free shipping over')
        ->assertDontSee('Orders In Transit')
        ->assertDontSee('Global Notices');
});

test('admin can save the shipping thresholds and delivery window', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post('/admin/shipping/settings', [
            'free_ship_over' => '65.50',
            'ship_cost' => '5.25',
            'included_weight_kg' => '2.5',
            'surcharge_per_kg' => '1.75',
            'delivery_min_days' => '3',
            'delivery_max_days' => '8',
        ], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJson(['status' => 'success']);

    expect(Setting::get('free_ship_over'))->toBe('65.5')
        ->and(Setting::get('ship_cost'))->toBe('5.25')
        ->and(Setting::get('ship_included_weight_kg'))->toBe('2.5')
        ->and(Setting::get('ship_surcharge_per_kg'))->toBe('1.75')
        ->and(Setting::get('delivery_min_days'))->toBe('3')
        ->and(Setting::get('delivery_max_days'))->toBe('8');
});

test('customer dashboard shows active announcements for purchased products', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $product = announcementTestProduct();
    $order = announcementTestOrder($customer);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $announcement = Announcement::create([
        'title' => 'Dashboard product update',
        'message' => 'Here is useful news about a product you purchased.',
        'type' => 'good',
        'is_active' => true,
    ]);
    $announcement->categories()->attach($product->category_id);

    $this->actingAs($customer)
        ->get('/user/dashboard')
        ->assertOk()
        ->assertSee($announcement->title)
        ->assertSee($announcement->message)
        ->assertSee($product->category->name);
});

test('admin can manage a multi-category announcement over JSON routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $product = announcementTestProduct();
    $sameCategoryProduct = announcementTestProduct(Category::findOrFail($product->category_id));
    $secondProduct = announcementTestProduct();
    $order = announcementTestOrder($customer);
    foreach ([$sameCategoryProduct, $secondProduct] as $purchasedProduct) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $purchasedProduct->id,
            'product_name' => $purchasedProduct->name,
            'product_price' => 10,
            'qty' => 1,
            'line_total' => 10,
        ]);
    }

    $this->actingAs($admin)
        ->postJson(route('admin.announcements.store'), [
            'title' => 'Lipstick delay',
            'message' => 'Your selected product is delayed.',
            'type' => 'delay',
            'category_ids' => [$product->category_id, $secondProduct->category_id],
            'is_active' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('recipient_count', 1)
        ->assertJsonPath('announcement.title', 'Lipstick delay')
        ->assertJsonPath('announcement.categories.0.id', $product->category_id);

    $announcement = Announcement::firstOrFail();
    expect(Announcement::forCustomer($customer->id)->pluck('id')->all())->toBe([$announcement->id]);

    $this->postJson(route('admin.announcements.store'), [
        'title' => 'Legacy product target',
        'message' => 'Product targets are no longer supported.',
        'type' => 'info',
        'product_ids' => [$product->id],
    ])->assertUnprocessable();

    $this->patchJson(route('admin.announcements.update', $announcement), [
        'title' => 'Updated delivery notice',
        'message' => 'The delivery estimate has changed.',
        'type' => 'info',
        'category_ids' => [$secondProduct->category_id],
        'is_active' => true,
    ])->assertOk()->assertJsonPath('announcement.title', 'Updated delivery notice');

    $this->patchJson(route('admin.announcements.toggle', $announcement))
        ->assertOk()->assertJsonPath('is_active', false);
    expect($announcement->fresh()->is_active)->toBeFalse();

    $this->deleteJson(route('admin.announcements.delete', $announcement))->assertOk();
    expect(Announcement::count())->toBe(0);
});

test('announcement management lists are paginated and link to matching customer orders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $product = announcementTestProduct();
    $announcement = Announcement::create([
        'title' => 'Delivery delay',
        'message' => 'Your product shipment is delayed.',
        'type' => 'delay',
        'is_active' => true,
    ]);
    $announcement->categories()->attach($product->category_id);

    $order = announcementTestOrder($customer);
    $order->forceFill(['created_at' => now()->subDays(12)])->saveQuietly();
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);

    for ($index = 0; $index < 11; $index++) {
        Announcement::create([
            'title' => "Extra announcement {$index}",
            'message' => 'A paginated test notice.',
            'type' => 'info',
            'is_active' => true,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.announcements.manage'))
        ->assertOk()
        ->assertSee('Your announcements')
        ->assertSee('Total announcements')
        ->assertSee('Currently published')
        ->assertSee('Paused for later')
        ->assertSee('View recipient customers')
        ->assertSee('Next')
        ->assertDontSee('id="announcementForm"', false);

    $this->get(route('admin.announcements.customers', $announcement))
        ->assertOk()
        ->assertSee('Announcement Customers')
        ->assertSee($customer->name)
        ->assertSee($order->order_number)
        ->assertSee('Late')
        ->assertSee($product->category->name)
        ->assertSee($product->name);
});

test('customers can view only announcements for products they purchased', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $product = announcementTestProduct();
    $order = announcementTestOrder($customer);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_price' => 10,
        'qty' => 1,
        'line_total' => 10,
    ]);
    $announcement = Announcement::create([
        'title' => 'Your lipstick update',
        'message' => 'We have a delivery update for you.',
        'type' => 'delay',
        'is_active' => true,
    ]);
    $announcement->categories()->attach($product->category_id);

    $this->actingAs($customer)
        ->get(route('user.announcements'))
        ->assertOk()
        ->assertSee($announcement->title)
        ->assertSee($product->category->name)
        ->assertSee('Announcements');
});

test('admin announcement editor and manage page use category targets', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = announcementTestProduct();
    $announcement = Announcement::create([
        'title' => 'Category update',
        'message' => 'An update for your category.',
        'type' => 'info',
        'is_active' => false,
    ]);
    $announcement->categories()->attach($product->category_id);

    $this->actingAs($admin)
        ->get(route('admin.announcements', ['edit' => $announcement->id]))
        ->assertOk()
        ->assertSee('Target categories')
        ->assertSee('name="category_ids[]"', false)
        ->assertSee('aria-haspopup="listbox"', false)
        ->assertSee('announcementContent.addEventListener', false)
        ->assertDontSee("getElementById('noticeType').contains", false)
        ->assertDontSee('product_ids[]');

    $this->get(route('admin.announcements.manage'))
        ->assertOk()
        ->assertSee('<h1>Announcements</h1>', false)
        ->assertSee('Target categories')
        ->assertSee('Search titles or categories')
        ->assertSee($product->category->name)
        ->assertDontSee($product->name)
        ->assertSee('Notice Manager')
        ->assertSee('href="' . route('admin.announcements.manage') . '" class="k-link active"', false)
        ->assertDontSee('href="' . route('admin.announcements') . '" class="k-link active"', false);

    $this->get(route('admin.announcements'))
        ->assertOk()
        ->assertDontSee('href="'.route('admin.announcements.manage').'" class="k-link active"', false);
});
