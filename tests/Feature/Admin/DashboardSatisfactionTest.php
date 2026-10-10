<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

function dashboardSatisfactionProduct(): Product
{
    $category = Category::create([
        'name' => 'Satisfaction test category',
        'slug' => 'satisfaction-test-category',
    ]);

    return Product::create([
        'name' => 'Satisfaction test product',
        'slug' => 'satisfaction-test-product',
        'category_id' => $category->id,
        'price' => 20,
        'discount' => 0,
        'stock' => 1,
        'emoji' => '✦',
        'is_active' => true,
        'review_status' => 'approved',
    ]);
}

test('admin dashboard uses the average and count of approved customer reviews', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $product = dashboardSatisfactionProduct();

    foreach ([5, 4, 3] as $rating) {
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'name' => $customer->name,
            'rating' => $rating,
            'comment' => 'Customer feedback.',
        ]);
        $review->status = 'approved';
        $review->save();
    }

    $pendingReview = Review::create([
        'product_id' => $product->id,
        'user_id' => $customer->id,
        'name' => $customer->name,
        'rating' => 1,
        'comment' => 'Awaiting moderation.',
    ]);
    $pendingReview->status = 'pending';
    $pendingReview->save();

    $this->actingAs($admin)
        ->getJson(route('admin.dashboard.data'))
        ->assertOk()
        ->assertJsonPath('d.avgRating', 4)
        ->assertJsonPath('d.reviewsCount', 3);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-count="4"', false)
        ->assertSee('3 approved reviews')
        ->assertDontSee('base score');
});

test('admin dashboard shows no customer satisfaction base score when there are no approved reviews', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->getJson(route('admin.dashboard.data'))
        ->assertOk()
        ->assertJsonPath('d.avgRating', 0)
        ->assertJsonPath('d.reviewsCount', 0);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-count="0"', false)
        ->assertSee('No reviews')
        ->assertSee('Customer Satisfaction · no reviews yet')
        ->assertDontSee('4.9');
});

test('dashboard revenue refresh reflects newly paid orders and excludes unpaid orders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = [
        'order_number' => 'REV-'.strtoupper(bin2hex(random_bytes(5))),
        'name' => 'Revenue Test',
        'email' => 'revenue@example.test',
        'address' => '1 Test Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'payment_method' => 'paystack',
        'payment_status' => 'paid',
        'status' => 'processing',
    ];

    Order::create($order + ['total' => 42.50, 'paid_at' => now()]);
    $this->actingAs($admin)
        ->getJson(route('admin.dashboard.data'))
        ->assertOk()
        ->assertJsonPath('d.revenue', 42.5)
        ->assertJsonPath('d.revNow', 42.5)
        ->assertJsonPath('d.ordersCount', 1);

    Order::create([
        ...$order,
        'order_number' => 'REV-'.strtoupper(bin2hex(random_bytes(5))),
        'payment_status' => 'failed',
        'total' => 100,
        'paid_at' => null,
    ]);
    Order::create([
        ...$order,
        'order_number' => 'REV-'.strtoupper(bin2hex(random_bytes(5))),
        'total' => 17.25,
        'paid_at' => now(),
    ]);

    $this->getJson(route('admin.dashboard.data'))
        ->assertOk()
        ->assertJsonPath('d.revenue', 59.75)
        ->assertJsonPath('d.revNow', 59.75)
        ->assertJsonPath('d.ordersCount', 2);
});

test('new customer graph data refreshes over ajax for the last seven calendar days', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $today = now()->startOfDay();
    User::factory()->create(['role' => 'user', 'created_at' => $today->copy()->addHours(2)]);
    User::factory()->create(['role' => 'user', 'created_at' => $today->copy()->subDay()->addHours(4)]);
    User::factory()->create(['role' => 'user', 'created_at' => $today->copy()->subDays(8)]);

    $this->actingAs($admin)
        ->getJson(route('admin.dashboard.data'))
        ->assertOk()
        ->assertJsonPath('d.uVals', [0, 0, 0, 0, 0, 1, 1])
        ->assertJsonPath('d.totalCustomers', 3)
        ->assertJsonPath('d.newThisWeek', 2);

    User::factory()->create(['role' => 'user', 'created_at' => $today->copy()->addHours(6)]);

    $this->getJson(route('admin.dashboard.data'))
        ->assertOk()
        ->assertJsonPath('d.uVals', [0, 0, 0, 0, 0, 1, 2])
        ->assertJsonPath('d.totalCustomers', 4)
        ->assertJsonPath('d.newThisWeek', 3);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('id="usersChart"', false)
        ->assertSee('Daily new customer registrations for the last seven days', false)
        ->assertSee('No new customer registrations in the last 7 days', false);
});
