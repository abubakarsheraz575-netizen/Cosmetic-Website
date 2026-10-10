<?php

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use Illuminate\Support\Facades\Mail;

function createCommerceOrder(User $user, array $attributes = []): array
{
    $order = Order::create(array_merge([
        'user_id' => $user->id,
        'order_number' => 'GL-'.fake()->unique()->numerify('#####'),
        'name' => $user->name,
        'email' => $user->email,
        'phone' => '5550100',
        'address' => '1 Test Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'subtotal' => 20,
        'shipping' => 3,
        'total' => 23,
        'payment_method' => 'stripe',
        'payment_status' => 'paid',
        'status' => 'delivered',
    ], $attributes));

    $item = $order->items()->create([
        'product_name' => 'Glow Serum',
        'product_price' => 10,
        'qty' => 2,
        'line_total' => 20,
    ]);

    return [$order, $item];
}

test('e-commerce admin pages render reports, returns, shipments, and invoices', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (['reports', 'returns', 'shipments', 'invoices'] as $page) {
        $this->actingAs($admin)
            ->get(route('admin.ecommerce.'.$page))
            ->assertOk()
            ->assertSee('E-commerce')
            ->assertSee('class="stats"', false);
    }
});

test('reports show charts before the paid-orders table and render the empty return state', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.ecommerce.reports'))
        ->assertOk()
        ->assertSeeInOrder(['class="stats"', 'class="charts"', 'Recent paid orders'])
        ->assertSee('No returns yet')
        ->assertSee('data: hasReturns ? returnValues : [1]', false);
});

test('paid invoices and shipments display the current product image when available', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    [$order, $item] = createCommerceOrder($customer);
    $category = Category::create(['name' => 'Image Test', 'slug' => 'image-test-'.uniqid()]);
    $product = Product::create([
        'name' => 'Invoice Image Serum',
        'slug' => 'invoice-image-serum-'.uniqid(),
        'category_id' => $category->id,
        'price' => 10,
        'discount' => 0,
        'stock' => 5,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    ProductImage::create(['product_id' => $product->id, 'path' => 'products/invoice-image.jpg', 'sort_order' => 0]);
    $item->update(['product_id' => $product->id, 'product_name' => $product->name]);
    $imageUrl = asset('storage/products/invoice-image.jpg');

    $this->actingAs($admin)->get(route('admin.ecommerce.invoices'))
        ->assertOk()
        ->assertSee($imageUrl)
        ->assertSee('Invoice Image Serum');
    $this->actingAs($admin)->get(route('admin.ecommerce.shipments'))
        ->assertOk()
        ->assertSee($imageUrl)
        ->assertSee('Invoice Image Serum');
});

test('customers can request returns for their own delivered paid order items', function () {
    Mail::fake();
    $customer = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    [$order, $item] = createCommerceOrder($customer);

    $this->actingAs($customer)->get(route('user.orders'))
        ->assertOk()
        ->assertSee('href="'.route('user.returns', ['order' => $order->id]).'#return-order-'.$order->id.'">', false)
        ->assertSee('Return the product')
        ->assertSee('Need Help?')
        ->assertSee('Need help with this order?')
        ->assertSee('Contact support')
        ->assertSee('data-order-item="'.$item->id.'"', false)
        ->assertSee('Request or track a return');

    $this->actingAs($customer)
        ->post(route('user.orders.returns.store', [$order, $item]), [
            'quantity' => 1,
            'reason' => 'The product caused irritation.',
        ])
        ->assertRedirect();
    Mail::assertSent(AdminActivityNotification::class, function (AdminActivityNotification $mail) use ($admin, $customer) {
        return $mail->activityTitle === 'New product return request'
            && $mail->hasTo($admin->email)
            && str_contains($mail->details['Customer'], $customer->email)
            && in_array('The product caused irritation.', $mail->details, true);
    });

    $return = OrderReturn::firstOrFail();
    expect($return->user_id)->toBe($customer->id)
        ->and($return->quantity)->toBe(1)
        ->and($return->reason)->toBe('The product caused irritation.')
        ->and($return->return_number)->toBe('RT-'.str_pad((string) $return->id, 6, '0', STR_PAD_LEFT));

    $this->actingAs($customer)->get(route('user.returns'))
        ->assertOk()
        ->assertSee($return->return_number)
        ->assertSee('Request returns for paid, delivered products and track their review.')
        ->assertDontSee('name="reason"', false);
});

test('the per-order return action is available only for delivered paid orders', function () {
    $customer = User::factory()->create(['role' => 'user']);
    createCommerceOrder($customer, ['order_number' => 'GL-SHIPPED-RETURN', 'status' => 'shipped']);

    $this->actingAs($customer)->get(route('user.orders'))
        ->assertOk()
        ->assertDontSee('Return the product')
        ->assertSee('Need Help?');
});

test('customers can request returns from the returns page for paid delivered items only', function () {
    $customer = User::factory()->create(['role' => 'user']);
    [$eligibleOrder, $eligibleItem] = createCommerceOrder($customer, ['order_number' => 'GL-ELIGIBLE-01']);
    [$unpaidOrder, $unpaidItem] = createCommerceOrder($customer, [
        'order_number' => 'GL-UNPAID-01',
        'payment_status' => 'pending',
    ]);
    [$undeliveredOrder, $undeliveredItem] = createCommerceOrder($customer, [
        'order_number' => 'GL-UNDELIVERED-01',
        'status' => 'shipped',
    ]);

    $this->actingAs($customer)->get(route('user.returns'))
        ->assertOk()
        ->assertSee('name="reason"', false)
        ->assertSee(route('user.orders.returns.store', [$eligibleOrder, $eligibleItem]))
        ->assertSee('GL-ELIGIBLE-01')
        ->assertDontSee('GL-UNPAID-01')
        ->assertDontSee('GL-UNDELIVERED-01');

    $this->actingAs($customer)->get(route('user.returns', ['order' => $eligibleOrder->id]))
        ->assertOk()
        ->assertSee('target-order')
        ->assertSee('Choose a delivered item below');

    $this->actingAs($customer)->get(route('user.returns', ['order' => $unpaidOrder->id]))->assertNotFound();

    $this->actingAs($customer)
        ->post(route('user.orders.returns.store', [$eligibleOrder, $eligibleItem]), [
            'quantity' => 1,
            'reason' => 'The product was not suitable.',
        ])
        ->assertRedirect();

    $this->actingAs($customer)->get(route('user.returns'))
        ->assertOk()
        ->assertSee('RT-000001')
        ->assertSee('pending')
        ->assertSee('No delivered paid products are currently eligible for a new return request.');
});

test('customers cannot request returns for another user or undelivered orders', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $otherCustomer = User::factory()->create(['role' => 'user']);
    [$otherOrder, $otherItem] = createCommerceOrder($otherCustomer);

    $this->actingAs($customer)
        ->post(route('user.orders.returns.store', [$otherOrder, $otherItem]), [
            'quantity' => 1,
            'reason' => 'I no longer need this product.',
        ])
        ->assertNotFound();

    [$pendingOrder, $pendingItem] = createCommerceOrder($customer, ['status' => 'shipped']);
    $this->actingAs($customer)
        ->post(route('user.orders.returns.store', [$pendingOrder, $pendingItem]), [
            'quantity' => 1,
            'reason' => 'I no longer need this product.',
        ])
        ->assertStatus(422);
});

test('admins can edit invoice lines and totals are recalculated', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    [$order, $item] = createCommerceOrder($customer, [
        'discount' => 2,
        'coupon_code' => 'CPN-SAVE',
        'coupon_discount_type' => 'percent',
        'coupon_discount_value' => 10,
        'tax_total' => 1.8,
        'tax_breakdown' => [['name' => 'GST', 'percentage' => 5, 'amount' => 0.9], ['name' => 'Service tax', 'percentage' => 5, 'amount' => 0.9]],
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.ecommerce.orders.update', $order), [
            'name' => $order->name,
            'email' => $order->email,
            'phone' => $order->phone,
            'address' => $order->address,
            'city' => $order->city,
            'postal_code' => $order->postal_code,
            'country' => $order->country,
            'shipping' => 4.50,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'notes' => '',
            'items' => [
                ['id' => $item->id, 'product_name' => 'Glow Serum Plus', 'qty' => 3, 'product_price' => 20],
            ],
        ])
        ->assertRedirect(route('admin.ecommerce.invoices'));

    expect((float) $order->fresh()->subtotal)->toBe(60.0)
        ->and((float) $order->fresh()->discount)->toBe(6.0)
        ->and((float) $order->fresh()->tax_total)->toBe(5.4)
        ->and((float) $order->fresh()->total)->toBe(63.9)
        ->and($order->fresh()->coupon_discount_type)->toBe('percent')
        ->and($order->fresh()->coupon_code)->toBe('CPN-SAVE')
        ->and((float) $order->fresh()->tax_breakdown[0]['amount'])->toBe(2.7)
        ->and($order->fresh()->status)->toBe('shipped')
        ->and($item->fresh()->product_name)->toBe('Glow Serum Plus')
        ->and((float) $item->fresh()->line_total)->toBe(60.0);
});

test('admins can update and delete return requests from dedicated pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    [$order, $item] = createCommerceOrder($customer);
    $return = OrderReturn::create([
        'return_number' => 'RT-000001',
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'user_id' => $customer->id,
        'quantity' => 1,
        'reason' => 'Product arrived damaged.',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->get(route('admin.ecommerce.returns.edit', $return))->assertOk();
    $this->actingAs($admin)->patch(route('admin.ecommerce.returns.update', $return), [
        'status' => 'refunded',
        'admin_note' => 'Refund issued.',
    ])->assertRedirect();

    expect($return->fresh()->status)->toBe('refunded');

    $this->actingAs($admin)->delete(route('admin.ecommerce.returns.destroy', $return))->assertRedirect();
    expect(OrderReturn::count())->toBe(0);
});

test('invoices, shipments, and report tables include paid orders only', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    createCommerceOrder($customer, ['order_number' => 'GL-PAID-100']);
    createCommerceOrder($customer, [
        'order_number' => 'GL-UNPAID-200',
        'payment_status' => 'pending',
    ]);

    foreach (['admin.ecommerce.invoices', 'admin.ecommerce.shipments', 'admin.ecommerce.reports'] as $routeName) {
        $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('GL-PAID-100')
            ->assertDontSee('GL-UNPAID-200');
    }

    $this->actingAs($admin)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get(route('admin.ecommerce.reports'))
        ->assertOk()
        ->assertSee('id="reportsOrdersTable"', false)
        ->assertSee('id="revenueChart"', false)
        ->assertSee('id="statusChart"', false)
        ->assertSee('id="returnsChart"', false);
});
