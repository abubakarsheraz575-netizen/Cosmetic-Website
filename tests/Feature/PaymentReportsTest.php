<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

function createPaymentReportOrder(User|null $user, array $attributes = []): Order
{
    static $orderNumber = 0;

    return Order::create(array_merge([
        'user_id' => $user?->id,
        'order_number' => 'PAY-REPORT-'.(++$orderNumber),
        'name' => $user?->name ?? 'Guest Buyer',
        'email' => $user?->email ?? 'guest@example.com',
        'phone' => '5550100',
        'address' => '1 Test Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'subtotal' => 20,
        'shipping' => 0,
        'total' => 20,
        'payment_method' => 'stripe',
        'payment_status' => 'paid',
        'status' => 'processing',
        'transaction_id' => 'TX-REPORT-'.str_pad((string) $orderNumber, 4, '0', STR_PAD_LEFT),
    ], $attributes));
}

test('payment transactions and logs show only paid customer and vendor orders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user', 'name' => 'Paid Customer']);
    $vendor = User::factory()->create(['role' => 'user', 'name' => 'Vendor Buyer']);
    $category = Category::create(['name' => 'Payment Test', 'slug' => 'payment-test']);
    Product::create([
        'name' => 'Vendor-owned product',
        'slug' => 'vendor-owned-payment-test',
        'category_id' => $category->id,
        'price' => 20,
        'discount' => 0,
        'stock' => 5,
        'is_active' => false,
        'submitted_by' => $vendor->id,
        'review_status' => 'pending',
    ]);
    createPaymentReportOrder($customer, [
        'order_number' => 'PAY-CUSTOMER-STRIPE',
        'transaction_id' => 'pi_customer_paid',
        'payment_method' => 'stripe',
    ]);
    createPaymentReportOrder($vendor, [
        'order_number' => 'PAY-VENDOR-PAYSTACK',
        'transaction_id' => 'GL-vendor-paid',
        'payment_method' => 'paystack',
    ]);
    createPaymentReportOrder(null, [
        'order_number' => 'PAY-GUEST-STRIPE',
        'transaction_id' => 'pi_guest_paid',
        'payment_method' => 'stripe',
    ]);
    createPaymentReportOrder($customer, [
        'order_number' => 'PAY-UNPAID-STRIPE',
        'transaction_id' => 'pi_not_paid',
        'payment_status' => 'pending',
    ]);

    $this->actingAs($admin)->get(route('admin.payment.transactions'))
        ->assertOk()
        ->assertSee('PAY-CUSTOMER-STRIPE')
        ->assertSee('PAY-VENDOR-PAYSTACK')
        ->assertSee('PAY-GUEST-STRIPE')
        ->assertDontSee('PAY-UNPAID-STRIPE')
        ->assertDontSee('Buyer type')
        ->assertDontSee('All buyers')
        ->assertSee('pi_customer_paid');

    $this->actingAs($admin)->get(route('admin.payment.logs'))
        ->assertOk()
        ->assertDontSee('All gateway payments')
        ->assertSee('Stripe')
        ->assertSee('Paystack')
        ->assertDontSee('PAY-CUSTOMER-STRIPE')
        ->assertDontSee('PAY-VENDOR-PAYSTACK')
        ->assertSee('Choose Stripe or Paystack');

    $this->actingAs($admin)->get(route('admin.payment.logs', ['provider' => 'paystack']))
        ->assertOk()
        ->assertSee('PAY-VENDOR-PAYSTACK')
        ->assertSee('Vendor Buyer')
        ->assertDontSee('PAY-CUSTOMER-STRIPE')
        ->assertDontSee('PAY-UNPAID-STRIPE');

    $this->actingAs($admin)->get(route('admin.payment.logs', ['provider' => 'stripe']))
        ->assertOk()
        ->assertSee('PAY-CUSTOMER-STRIPE')
        ->assertSee('PAY-GUEST-STRIPE')
        ->assertDontSee('PAY-VENDOR-PAYSTACK');

    $this->actingAs($admin)->get(route('admin.payment.transactions', ['provider' => 'stripe']))
        ->assertOk()
        ->assertSee(route('admin.ecommerce.orders.edit', Order::where('order_number', 'PAY-CUSTOMER-STRIPE')->first()), false)
        ->assertSee(route('admin.payment.transactions.destroy', Order::where('order_number', 'PAY-CUSTOMER-STRIPE')->first()), false);
});

test('users table keeps its own search and pagination without duplicate datatable controls', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.users'))
        ->assertOk()
        ->assertSee('<table data-server-pagination="true">', false)
        ->assertSee('id="usrSearch"', false)
        ->assertSee('id="usrPgn"', false);
});

test('admins can record edit and delete clearly marked manual paid transactions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user', 'name' => 'Manual Buyer']);
    $category = Category::create(['name' => 'Manual Payment Test', 'slug' => 'manual-payment-test']);
    $product = Product::create([
        'name' => 'Manual product',
        'slug' => 'manual-product',
        'category_id' => $category->id,
        'price' => 18,
        'discount' => 0,
        'stock' => 5,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    $product->images()->create(['path' => 'products/manual-test.jpg']);

    $this->actingAs($admin)->post(route('admin.payment.transactions.store'), [
        'user_id' => $customer->id,
        'name' => $customer->name,
        'email' => $customer->email,
        'amount' => '18.00',
        'product_id' => $product->id,
        'paid_at' => now()->format('Y-m-d H:i:s'),
        'payment_note' => 'Received in store',
    ])->assertRedirect(route('admin.payment.transactions'));

    $order = Order::where('is_manual_payment', true)->firstOrFail();
    expect($order->payment_status)->toBe('paid')
        ->and($order->payment_method)->toBe('manual')
        ->and($order->transaction_id)->toBe('MAN-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));

    $this->actingAs($admin)->get(route('admin.payment.transactions'))
        ->assertOk()
        ->assertSee('MANUAL')
        ->assertSee('Manual product')
        ->assertSee('storage/products/manual-test.jpg');

    $this->actingAs($admin)->patch(route('admin.payment.transactions.update', $order), [
        'user_id' => $customer->id,
        'name' => $customer->name,
        'email' => $customer->email,
        'amount' => '21.00',
        'product_id' => $product->id,
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ])->assertRedirect(route('admin.payment.transactions'));

    expect((float) $order->fresh()->total)->toBe(21.0);

    $this->from(route('admin.payment.transactions'))
        ->delete(route('admin.payment.transactions.destroy', $order))
        ->assertRedirect(route('admin.payment.transactions'));
    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
});
