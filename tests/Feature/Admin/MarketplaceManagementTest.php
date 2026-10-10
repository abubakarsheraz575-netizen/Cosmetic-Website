<?php

use App\Models\Category;
use App\Models\MarketplaceMessage;
use App\Models\MarketplaceWithdrawal;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SupportMessage;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use App\Mail\MarketplacePayoutStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function marketplaceVendor(string $name = 'Marketplace Vendor', array $attributes = []): User
{
    $vendor = User::factory()->create(array_merge([
        'name' => $name,
        'email_verified_at' => now(),
        'phone' => '+447700900001',
    ], $attributes));
    $category = Category::firstOrCreate(
        ['slug' => 'marketplace-test-category'],
        ['name' => 'Marketplace Category', 'icon' => '✦', 'description' => '', 'is_active' => true]
    );
    Product::create([
        'name' => $name.' Product',
        'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
        'description' => 'Marketplace test product',
        'category_id' => $category->id,
        'price' => 20,
        'discount' => 0,
        'stock' => 5,
        'emoji' => '✦',
        'is_active' => true,
        'submitted_by' => $vendor->id,
        'review_status' => 'approved',
    ]);

    return $vendor;
}

test('marketplace directory pages list vendor accounts and expose operations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = marketplaceVendor();
    $unverified = marketplaceVendor('New Unverified Vendor', ['email_verified_at' => null]);

    $this->actingAs($admin)->get(route('admin.marketplace.stores'))
        ->assertOk()->assertSee($vendor->name)->assertSee($unverified->name)
        ->assertSee('data-marketplace-confirm', false);
    $this->actingAs($admin)->get(route('admin.marketplace.vendors'))
        ->assertOk()->assertSee($vendor->name)->assertDontSee($unverified->name);
    $this->actingAs($admin)->get(route('admin.marketplace.vendors.unverified'))
        ->assertOk()->assertSee($unverified->name)->assertDontSee($vendor->name);
    $this->actingAs($admin)->get(route('admin.marketplace.vendors.show', $vendor))
        ->assertOk()->assertSee($vendor->email)->assertSee($vendor->phone)->assertSee('Submitted products');
});

test('admins can block vendors and delete the account plus vendor submitted products', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = marketplaceVendor();
    $productId = $vendor->submittedProducts()->value('id');

    $this->actingAs($admin)->patch(route('admin.marketplace.vendors.status', $vendor), ['is_blocked' => 1])
        ->assertRedirect();
    expect($vendor->fresh()->is_blocked)->toBeTrue();

    $this->actingAs($admin)->delete(route('admin.marketplace.vendors.destroy', $vendor))
        ->assertRedirect(route('admin.marketplace.stores'));
    expect(User::find($vendor->id))->toBeNull()
        ->and(Product::find($productId))->toBeNull();
});

test('vendor messages are private from customer support and accessible only to marketplace vendors', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = marketplaceVendor();
    $customer = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.marketplace.messages', ['vendor' => $customer->id]))
        ->assertNotFound();
    $this->actingAs($admin)->post(route('admin.marketplace.messages.send'), [
        'vendor_id' => $customer->id,
        'body' => 'This must not be delivered as a marketplace message.',
    ])->assertNotFound();

    $this->actingAs($admin)->post(route('admin.marketplace.messages.send'), [
        'vendor_id' => $vendor->id,
        'body' => 'Please update your listing.',
    ])->assertRedirect(route('admin.marketplace.messages', ['vendor' => $vendor->id]));
    $message = MarketplaceMessage::firstOrFail();
    expect($message->body)->toBe('Please update your listing.')
        ->and(SupportMessage::count())->toBe(0);

    $this->actingAs($customer)->get(route('user.marketplace'))->assertNotFound();
    $this->actingAs($vendor)->get(route('user.marketplace'))->assertOk()
        ->assertSee('Please update your listing.');
    $this->actingAs($vendor)->post(route('user.marketplace.messages.send'), [
        'body' => 'Thanks, I will update it.',
    ])->assertRedirect();
    expect(MarketplaceMessage::count())->toBe(2)
        ->and(SupportMessage::count())->toBe(0);
});

test('vendors request an encrypted payout for a verified paid order and admins accept then complete it', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = marketplaceVendor();
    $destination = 'Example payout account 123456';
    $product = $vendor->submittedProducts()->firstOrFail();
    $buyer = User::factory()->create();
    $order = Order::create([
        'user_id' => $buyer->id,
        'order_number' => 'MP-'.strtoupper(bin2hex(random_bytes(5))),
        'name' => $buyer->name,
        'email' => $buyer->email,
        'phone' => '+447700900000',
        'address' => '1 Paid Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'subtotal' => 20,
        'total' => 24.99,
        'shipping' => 4.99,
        'payment_method' => 'stripe',
        'payment_status' => 'paid',
        'status' => 'processing',
        'transaction_id' => 'pi_marketplace_verified_001',
        'paid_at' => now(),
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_price' => 20,
        'qty' => 1,
        'line_total' => 20,
    ]);

    $this->actingAs($vendor)->post(route('user.marketplace.withdrawals.store'), [
        'order_id' => $order->id,
        'payout_method' => 'Bank transfer',
        'payout_details' => $destination,
    ])->assertRedirect();

    $withdrawal = MarketplaceWithdrawal::firstOrFail();
    expect($withdrawal->payout_details)->toBe($destination)
        ->and(DB::table('marketplace_withdrawals')->where('id', $withdrawal->id)->value('payout_details'))->not->toBe($destination)
        ->and($withdrawal->order_id)->toBe($order->id)
        ->and($withdrawal->payment_transaction_id)->toBe('pi_marketplace_verified_001')
        ->and((float) $withdrawal->sales_amount)->toBe(20.0)
        ->and((float) $withdrawal->commission_rate)->toBe(10.0)
        ->and((float) $withdrawal->commission_amount)->toBe(2.0)
        ->and((float) $withdrawal->amount)->toBe(18.0);
    Mail::assertSent(AdminActivityNotification::class, fn (AdminActivityNotification $mail) => $mail->activityTitle === 'Marketplace payout requested' && $mail->hasTo($admin->email));

    $this->actingAs($vendor)->get(route('user.marketplace.withdrawals'))
        ->assertOk()
        ->assertSee('Paid order')
        ->assertSee($order->order_number)
        ->assertSee('£18.00')
        ->assertSee('pending');

    $this->actingAs($admin)->get(route('admin.marketplace.withdrawals'))
        ->assertOk()
        ->assertSee($destination)
        ->assertSee('pi_marketplace_verified_001')
        ->assertSee('Customer payment verified')
        ->assertSee('Save commission')
        ->assertSee('Accept');
    $this->actingAs($admin)->patch(route('admin.marketplace.withdrawals.update', $withdrawal), [
        'status' => 'processing',
    ])->assertRedirect();
    expect($withdrawal->fresh()->status)->toBe('processing');
    $this->patch(route('admin.marketplace.withdrawals.update', $withdrawal), [
        'status' => 'completed',
    ])->assertRedirect();
    expect($withdrawal->fresh()->status)->toBe('completed');
    Mail::assertSent(MarketplacePayoutStatusNotification::class, 2);
});

test('admin commission is dynamic and order discounts are allocated by seller product share', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = marketplaceVendor('Commission Vendor');
    $otherVendor = marketplaceVendor('Other Commission Vendor');
    $buyer = User::factory()->create();
    $order = Order::create([
        'user_id' => $buyer->id,
        'order_number' => 'MP-'.strtoupper(bin2hex(random_bytes(5))),
        'name' => $buyer->name,
        'email' => $buyer->email,
        'address' => '1 Paid Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'subtotal' => 40,
        'discount' => 10,
        'total' => 30,
        'payment_method' => 'paystack',
        'payment_status' => 'paid',
        'transaction_id' => 'GL_verified_marketplace_reference',
        'paid_at' => now(),
    ]);
    foreach ([$vendor, $otherVendor] as $seller) {
        $product = $seller->submittedProducts()->firstOrFail();
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 20,
            'qty' => 1,
            'line_total' => 20,
        ]);
    }

    $this->actingAs($admin)->patch(route('admin.marketplace.withdrawals.commission'), [
        'commission_rate' => '15',
    ])->assertRedirect();
    expect(Setting::marketplaceCommissionRate())->toBe(15.0);

    $this->actingAs($vendor)->get(route('user.marketplace.withdrawals'))
        ->assertOk()
        ->assertSee('15.00%')
        ->assertSee('£15.00')
        ->assertSee('£12.75');
    $this->post(route('user.marketplace.withdrawals.store'), [
        'order_id' => $order->id,
        'payout_method' => 'PayPal',
        'payout_details' => 'commission-vendor@example.test',
    ])->assertRedirect();

    $withdrawal = MarketplaceWithdrawal::firstOrFail();
    expect((float) $withdrawal->sales_amount)->toBe(15.0)
        ->and((float) $withdrawal->coupon_discount_share)->toBe(5.0)
        ->and((float) $withdrawal->commission_rate)->toBe(15.0)
        ->and((float) $withdrawal->commission_amount)->toBe(2.25)
        ->and((float) $withdrawal->amount)->toBe(12.75);
    $this->actingAs($admin)->get(route('admin.marketplace.withdrawals', ['status' => 'pending']))
        ->assertOk()->assertSee('Pending')->assertSee('£12.75');
});

test('vendors cannot request arbitrary or unpaid sales and admins cannot approve unverified legacy requests', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = marketplaceVendor('Verified Vendor');
    $otherVendor = marketplaceVendor('Different Vendor');
    $buyer = User::factory()->create();
    $order = Order::create([
        'user_id' => $buyer->id,
        'order_number' => 'MP-'.strtoupper(bin2hex(random_bytes(5))),
        'name' => $buyer->name,
        'email' => $buyer->email,
        'address' => '1 Pending Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'subtotal' => 20,
        'total' => 20,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'transaction_id' => 'pi_not_yet_paid',
    ]);
    $product = $vendor->submittedProducts()->firstOrFail();
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_price' => 20,
        'qty' => 1,
        'line_total' => 20,
    ]);

    $this->actingAs($vendor)->get(route('user.marketplace.withdrawals'))->assertDontSee($order->order_number);
    $this->post(route('user.marketplace.withdrawals.store'), [
        'order_id' => $order->id,
        'payout_method' => 'Bank transfer',
        'payout_details' => 'vendor@example.test',
    ])->assertSessionHasErrors('order_id');
    $this->post(route('user.marketplace.withdrawals.store'), [
        'order_id' => 987654,
        'payout_method' => 'Bank transfer',
        'payout_details' => 'vendor@example.test',
    ])->assertSessionHasErrors('order_id');
    expect(MarketplaceWithdrawal::count())->toBe(0);

    $legacy = MarketplaceWithdrawal::create([
        'vendor_id' => $otherVendor->id,
        'amount' => 10,
        'payout_method' => 'Other',
        'payout_details' => 'legacy@example.test',
        'status' => 'pending',
    ]);
    $this->actingAs($admin)->patch(route('admin.marketplace.withdrawals.update', $legacy), [
        'status' => 'processing',
    ])->assertStatus(422);
    $this->patch(route('admin.marketplace.withdrawals.update', $legacy), [
        'status' => 'rejected',
    ])->assertRedirect();
    expect($legacy->fresh()->status)->toBe('rejected');

    $this->patch(route('admin.marketplace.withdrawals.commission'), [
        'commission_rate' => 101,
    ])->assertSessionHasErrors('commission_rate');
});
