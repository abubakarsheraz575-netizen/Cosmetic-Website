<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;

function makeAdminNotificationData(): array
{
    $customer = User::factory()->create([
        'role' => 'user',
        'name' => 'Notification Customer',
    ]);
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Notification Category',
        'slug' => 'notification-category',
    ]);
    $size = Size::create(['name' => 'Notification Size', 'ml' => 30]);
    $product = Product::create([
        'name' => 'Low Stock Product',
        'slug' => 'low-stock-product',
        'category_id' => $category->id,
        'size_id' => $size->id,
        'price' => 15,
        'stock' => 4,
    ]);
    $order = Order::create([
        'user_id' => $customer->id,
        'order_number' => 'NOTIFY-10001',
        'name' => $customer->name,
        'email' => $customer->email,
        'address' => '1 Test Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'UK',
        'subtotal' => 15,
        'shipping' => 0,
        'total' => 15,
        'payment_method' => 'card',
        'payment_status' => 'paid',
        'status' => 'processing',
    ]);

    return compact('admin', 'customer', 'order', 'product');
}

test('admin notification dropdown and view all page use current store records', function () {
    $data = makeAdminNotificationData();

    $this->actingAs($data['admin'])
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('id="nbNotifList"', false)
        ->assertSee('View all notifications');

    $this->actingAs($data['admin'])
        ->getJson('/admin/notifications/feed')
        ->assertOk()
        ->assertJsonPath('unread_count', 4)
        ->assertJsonFragment(['title' => 'New order received #NOTIFY-10001'])
        ->assertJsonFragment(['title' => 'Payment confirmed'])
        ->assertJsonFragment(['title' => 'Low stock warning'])
        ->assertJsonFragment(['title' => 'New customer registered']);

    $this->actingAs($data['admin'])
        ->get('/admin/notifications')
        ->assertOk()
        ->assertSee('Low Stock Product')
        ->assertSee('Mark all as read');
});

test('admin can mark a notification read and mark all notifications read', function () {
    $data = makeAdminNotificationData();
    $key = 'order-created:'.$data['order']->id;

    $this->actingAs($data['admin'])
        ->postJson('/admin/notifications/read', ['notification_key' => $key])
        ->assertOk()
        ->assertJsonPath('ok', true);

    $this->actingAs($data['admin'])
        ->getJson('/admin/notifications/feed')
        ->assertJsonPath('unread_count', 3);

    $this->actingAs($data['admin'])
        ->postJson('/admin/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('read_count', 4);

    $this->actingAs($data['admin'])
        ->getJson('/admin/notifications/feed')
        ->assertJsonPath('unread_count', 0);

    $otherAdmin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($otherAdmin)
        ->getJson('/admin/notifications/feed')
        ->assertJsonPath('unread_count', 4);
});

test('admin cannot mark a notification read unless it exists in the feed', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->postJson('/admin/notifications/read', ['notification_key' => 'unknown:999'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('notification_key');
});
