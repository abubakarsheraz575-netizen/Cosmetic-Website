<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use App\Mail\OrderReceipt;
use App\Mail\MarketplaceSaleNotification;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

function couponTestProduct(float $price = 50, int $stock = 10): Product
{
    $category = Category::create([
        'name' => 'Coupon Test',
        'slug' => 'coupon-test-'.uniqid(),
    ]);

    return Product::create([
        'name' => 'Test moisturizer',
        'slug' => 'test-moisturizer-'.uniqid(),
        'category_id' => $category->id,
        'price' => $price,
        'discount' => 0,
        'stock' => $stock,
        'is_active' => true,
        'review_status' => 'approved',
    ]);
}

function checkoutFormData(string $couponCode, string $paymentMethod = 'stripe'): array
{
    return [
        'name' => 'Customer Test',
        'email' => 'customer@example.test',
        'phone' => '555-0100',
        'address' => '1 Test Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'payment_method' => $paymentMethod,
        'coupon_code' => $couponCode,
    ];
}

function couponTestRecord(array $attributes = []): Coupon
{
    return Coupon::create(array_merge([
        'code' => 'CPN-'.strtoupper(bin2hex(random_bytes(6))),
        'discount_type' => 'percent',
        'discount_value' => 10,
        'usage_limit' => 5,
        'uses_count' => 0,
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addDay(),
        'is_active' => true,
    ], $attributes));
}

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('shopping bag and checkout require a signed-in customer', function () {
    $product = couponTestProduct(19.5);
    auth()->logout();

    $this->postJson(route('cart.add'), ['product_id' => $product->id])
        ->assertUnauthorized();

    $this->get(route('cart.index'))
        ->assertRedirect(route('login'));

    $this->get(route('checkout'))
        ->assertRedirect(route('login'));

    $this->getJson(route('cart.items'))->assertUnauthorized();
    $this->postJson(route('cart.update'), ['product_id' => $product->id, 'qty' => 1])->assertUnauthorized();
    $this->postJson(route('cart.remove'), ['product_id' => $product->id])->assertUnauthorized();
    $this->postJson(route('checkout.coupon'), ['coupon_code' => 'ANY'])->assertUnauthorized();
    $this->postJson(route('checkout.process'), checkoutFormData(''))->assertUnauthorized();
    $this->postJson(route('checkout.confirm'), [])->assertUnauthorized();

    $this->actingAs(User::factory()->create())
        ->postJson(route('cart.add'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Your shopping bag')
        ->assertSee($product->name)
        ->assertSee('Continue to checkout')
        ->assertSee('Your account is required');

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('Your signed-in account is required');
});

test('Paystack checkout accepts a prefixed secret but sends only one Bearer authorization prefix', function () {
    $product = couponTestProduct(price: 20);
    Setting::set('paystack_key', 'pk_test_public');
    Setting::set('paystack_secret', Crypt::encryptString('Authorization: Bearer sk_test_secret'));
    Http::fake([
        'https://api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.test/start'],
        ], 200),
    ]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.process'), checkoutFormData('', 'paystack'))
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('paystack.authorization_url', 'https://checkout.paystack.test/start');

    Http::assertSent(fn (ClientRequest $request) => $request->hasHeader('Authorization', 'Bearer sk_test_secret'));
});

test('Paystack authorization failures explain raw-key configuration and do not disclose the secret', function () {
    $product = couponTestProduct(price: 20);
    Setting::set('paystack_key', 'pk_test_public');
    Setting::set('paystack_secret', Crypt::encryptString('sk_test_secret'));
    Http::fake([
        'https://api.paystack.co/transaction/initialize' => Http::response([
            'status' => false,
            'message' => 'Format is Authorization Bearer [secret key]',
        ], 401),
    ]);

    $response = $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.process'), checkoutFormData('', 'paystack'))
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Paystack rejected the secret key. In Admin → Payment Settings, save the raw secret key only (for example, sk_test_…), without “Authorization” or “Bearer”; the server formats the authentication header automatically.');

    expect($response->getContent())->not->toContain('sk_test_secret');
});

test('Paystack checkout diagnoses a malformed stored secret before calling Paystack', function () {
    $product = couponTestProduct(price: 20);
    Setting::set('paystack_key', 'pk_test_public');
    Setting::set('paystack_secret', Crypt::encryptString('not-a-paystack-secret'));
    Http::preventStrayRequests();

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.process'), checkoutFormData('', 'paystack'))
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Paystack key settings are invalid. In Admin → Payment Settings, enter the complete public key (pk_test_… or pk_live_…) and secret key (sk_test_… or sk_live_…). Paste the actual keys, not the Authorization/Bearer instructions; the server builds that header automatically.');
});

test('admin Paystack settings strip an optional Authorization Bearer prefix and keep the secret encrypted', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->patchJson(route('admin.payments.update'), [
        'paystack_key' => 'pk_test_public',
        'paystack_secret' => 'Authorization Bearer sk_test_secret',
        'paystack_currency' => 'NGN',
        'paystack_rate' => 2000,
    ])->assertOk()->assertJsonPath('status', 'success');

    expect(Crypt::decryptString(Setting::get('paystack_secret')))->toBe('sk_test_secret')
        ->and(Setting::get('paystack_mask'))->not->toContain('sk_test_secret');
});

test('admin rejects malformed Paystack credentials and mixed test/live keys', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->patchJson(route('admin.payments.update'), [
        'paystack_key' => 'pk_test_public',
        'paystack_secret' => 'Authorization Bearer [secret key]',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('paystack_secret');

    $this->patchJson(route('admin.payments.update'), [
        'paystack_key' => 'pk_test_public',
        'paystack_secret' => 'sk_live_secret',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('paystack_secret');

    expect(Setting::get('paystack_secret'))->toBeNull();
});

test('admin coupon issuance generates unique codes', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $couponData = [
        'discount_type' => 'percent',
        'discount_value' => 10,
        'usage_limit' => 5,
        'starts_at' => now()->subMinute()->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
        'is_active' => 1,
    ];

    foreach (range(1, 3) as $_) {
        $this->actingAs($admin)->post(route('admin.ecommerce.coupons.store'), $couponData)
            ->assertRedirect(route('admin.ecommerce.coupons'));
    }

    $codes = Coupon::pluck('code');
    expect($codes)->toHaveCount(3)
        ->and($codes->unique())->toHaveCount(3);
    foreach ($codes as $code) {
        expect($code)->toMatch('/^CPN-[A-Z0-9]{12}$/');
    }
    $this->actingAs($admin)->get(route('admin.ecommerce.coupons'))
        ->assertOk()
        ->assertSee((string) $codes->first())
        ->assertSee('Generate coupon');
});

test('checkout uses the configured shipping threshold and fee in both summary and free-shipping progress', function () {
    $product = couponTestProduct(30);
    Setting::set('free_ship_over', '40');
    Setting::set('ship_cost', '6.75');

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('£6.75')
        ->assertSee('£10.00')
        ->assertSee('FREE shipping');

    Setting::set('free_ship_over', '0');
    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('is applied to this order.')
        ->assertDontSee('Add £40.00 more for');
});

test('checkout charges each started kg above the cart allowance even when standard delivery is free', function () {
    $product = couponTestProduct(15);
    $product->update(['shipping_weight' => 1200, 'weight_unit' => 'g']);
    Setting::set('free_ship_over', '40');
    Setting::set('ship_cost', '6.75');
    Setting::set('ship_included_weight_kg', '1');
    Setting::set('ship_surcharge_per_kg', '2.5');
    $cart = [$product->id => ['qty' => 2]];

    $this->withSession(['cart' => $cart])
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('Extra weight (2 kg)')
        ->assertSee('Cart weight: 2.400 kg')
        ->assertSee('£10.00')
        ->assertSee('£6.75')
        ->assertSee('£5.00');

    Setting::set('free_ship_over', '0');
    $this->withSession(['cart' => $cart])
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('FREE standard delivery')
        ->assertSee('Extra weight (2 kg)')
        ->assertDontSee('£6.75')
        ->assertSee('£5.00');
});

test('coupon totals retain the cart weight surcharge', function () {
    $product = couponTestProduct(30);
    $product->update(['shipping_weight' => 1.2, 'weight_unit' => 'kg']);
    Setting::set('free_ship_over', '40');
    Setting::set('ship_cost', '6.75');
    Setting::set('ship_included_weight_kg', '1');
    Setting::set('ship_surcharge_per_kg', '2.5');
    $coupon = couponTestRecord(['discount_type' => 'percent', 'discount_value' => 10]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk()
        ->assertJsonPath('shipping', 9.25)
        ->assertJsonPath('weight_surcharge', 2.5)
        ->assertJsonPath('total', 36.25);
});

test('admins can edit and delete an issued coupon without changing its code', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $coupon = couponTestRecord(['discount_type' => 'percent', 'discount_value' => 10]);
    $code = $coupon->code;

    $this->actingAs($admin)->patch(route('admin.ecommerce.coupons.update', $coupon), [
        'discount_type' => 'fixed',
        'discount_value' => 12.5,
        'usage_limit' => 10,
        'starts_at' => now()->subMinute()->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
    ])->assertRedirect(route('admin.ecommerce.coupons'));

    expect($coupon->fresh()->code)->toBe($code)
        ->and($coupon->fresh()->discount_type)->toBe('fixed')
        ->and((float) $coupon->fresh()->discount_value)->toBe(12.5)
        ->and($coupon->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->delete(route('admin.ecommerce.coupons.destroy', $coupon))
        ->assertRedirect(route('admin.ecommerce.coupons'));
    expect(Coupon::count())->toBe(0);
});

test('admins can create edit deactivate and delete named percentage taxes', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.ecommerce.taxes.store'), [
        'name' => 'GST',
        'percentage' => 5,
        'is_active' => 1,
    ])->assertRedirect(route('admin.ecommerce.taxes'));

    $tax = TaxRate::firstOrFail();
    $this->actingAs($admin)->get(route('admin.ecommerce.taxes'))
        ->assertOk()
        ->assertSee('GST')
        ->assertSee('Add a tax');
    $this->actingAs($admin)->patch(route('admin.ecommerce.taxes.update', $tax), [
        'name' => 'Service tax',
        'percentage' => 7.5,
    ])->assertRedirect(route('admin.ecommerce.taxes'))->assertSessionHasNoErrors();
    expect($tax->fresh()->name)->toBe('Service tax')
        ->and((float) $tax->fresh()->percentage)->toBe(7.5)
        ->and($tax->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->delete(route('admin.ecommerce.taxes.destroy', $tax))
        ->assertRedirect(route('admin.ecommerce.taxes'));
    expect(TaxRate::count())->toBe(0);
});

test('coupon and tax registers search and paginate admin records', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    foreach (range(1, 12) as $index) {
        couponTestRecord(['code' => sprintf('CPN-PAGE-%02d', $index)]);
        TaxRate::create(['name' => 'Tax rate '.sprintf('%02d', $index), 'percentage' => 5, 'is_active' => true]);
    }

    $this->actingAs($admin)->get(route('admin.ecommerce.coupons', ['per_page' => 10]))
        ->assertOk()
        ->assertViewHas('coupons', fn ($coupons) => $coupons->total() === 12
            && $coupons->perPage() === 10
            && $coupons->count() === 10);
    $this->get(route('admin.ecommerce.coupons', ['search' => 'CPN-PAGE-03']))
        ->assertOk()
        ->assertSee('CPN-PAGE-03')
        ->assertViewHas('coupons', fn ($coupons) => $coupons->total() === 1);

    $this->get(route('admin.ecommerce.taxes', ['per_page' => 10]))
        ->assertOk()
        ->assertViewHas('taxes', fn ($taxes) => $taxes->total() === 12
            && $taxes->perPage() === 10
            && $taxes->count() === 10);
    $this->get(route('admin.ecommerce.taxes', ['search' => 'Tax rate 03']))
        ->assertOk()
        ->assertSee('Tax rate 03')
        ->assertViewHas('taxes', fn ($taxes) => $taxes->total() === 1);
});

test('expired and exhausted coupons are rejected at apply time', function () {
    couponTestRecord(['code' => 'CPN-EXPIRED', 'ends_at' => now()->subSecond()]);
    couponTestRecord(['code' => 'CPN-EXHAUSTED', 'uses_count' => 1, 'usage_limit' => 1]);

    $this->postJson(route('checkout.coupon'), ['coupon_code' => 'CPN-EXPIRED'])
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');
    $this->postJson(route('checkout.coupon'), ['coupon_code' => 'CPN-EXHAUSTED'])
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');
});

test('checkout process requires a coupon to be validated before accepting it', function () {
    $product = couponTestProduct(price: 20);
    $coupon = couponTestRecord(['code' => 'CPN-NEEDS-APPLY']);
    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');
    Http::fake(['https://api.stripe.com/*' => Http::response(['id' => 'pi_unexpected', 'client_secret' => 'secret'], 200)]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.process'), checkoutFormData($coupon->code))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('coupon_code');

    expect(Order::count())->toBe(0)
        ->and($coupon->fresh()->reserved_count)->toBe(0);
});

test('fixed amount coupons are capped by the merchandise subtotal', function () {
    $product = couponTestProduct(price: 20);
    $coupon = couponTestRecord([
        'code' => 'CPN-FIXED',
        'discount_type' => 'fixed',
        'discount_value' => 60,
    ]);
    TaxRate::create(['name' => 'GST', 'percentage' => 10, 'is_active' => true]);

    $this->withSession(['cart' => [$product->id => ['qty' => 2]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk()
        ->assertJsonPath('discount', 40)
        ->assertJsonPath('tax_total', 0)
        ->assertJsonPath('total', 4.99);
});

test('coupon use is reserved atomically while another payment is pending', function () {
    $product = couponTestProduct(price: 20);
    $coupon = couponTestRecord(['code' => 'CPN-ONLY-ONCE', 'usage_limit' => 1]);
    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');
    Http::fake(['https://api.stripe.com/*' => Http::response(['id' => 'pi_pending', 'client_secret' => 'secret'], 200)]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk();
    $this->postJson(route('checkout.process'), checkoutFormData($coupon->code))
        ->assertOk();
    $this->postJson(route('checkout.process'), checkoutFormData($coupon->code))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('coupon_code');

    expect(Order::count())->toBe(1)
        ->and($coupon->fresh()->uses_count)->toBe(0)
        ->and($coupon->fresh()->reserved_count)->toBe(1)
        ->and(CouponRedemption::firstOrFail()->status)->toBe('reserved');
});

test('checkout calculates and persists coupon discount and active taxes and redeems once', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $product = couponTestProduct();
    $coupon = couponTestRecord(['code' => 'CPN-SAVE-TEN']);
    TaxRate::create(['name' => 'GST', 'percentage' => 5, 'is_active' => true]);
    TaxRate::create(['name' => 'Service tax', 'percentage' => 2.5, 'is_active' => true]);
    TaxRate::create(['name' => 'Inactive tax', 'percentage' => 8, 'is_active' => false]);
    Setting::set('free_ship_over', 50);
    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');

    $this->withSession(['cart' => [$product->id => ['qty' => 2]]])
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('Have a coupon?')
        ->assertSee('name="coupon_code" id="appliedCouponCode"', false)
        ->assertDontSee('id="couponCode" maxlength="32" name=', false)
        ->assertSee('GST (5%)')
        ->assertSee('Service tax (2.5%)');
    $this->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk()
        ->assertJsonPath('discount', 10)
        ->assertJsonPath('tax_total', 6.75)
        ->assertJsonPath('total', 96.75)
        ->assertJsonPath('tax_breakdown.0.name', 'GST');

    Http::fake(function (ClientRequest $request) {
        if ($request->method() === 'POST') {
            return Http::response(['id' => 'pi_coupon_test', 'client_secret' => 'pi_coupon_test_secret'], 200);
        }

        return Http::response([
            'id' => 'pi_coupon_test',
            'status' => 'succeeded',
            'amount' => 9675,
            'currency' => 'gbp',
            'metadata' => ['order_id' => (string) Order::value('id')],
        ], 200);
    });

    test('confirmed marketplace product purchases email the buyer, admin, and only the relevant vendor', function () {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = User::factory()->create(['role' => 'user']);
        $buyer = auth()->user();
        $product = couponTestProduct(price: 20);
        $product->update(['submitted_by' => $vendor->id, 'review_status' => 'approved']);
        Setting::set('stripe_key', 'pk_test');
        Setting::set('stripe_secret', 'sk_test_secret');
        Http::fake(function (ClientRequest $request) use ($product) {
            if ($request->method() === 'POST') {
                return Http::response(['id' => 'pi_vendor_product_sale', 'client_secret' => 'pi_vendor_product_sale_secret'], 200);
            }

            $order = Order::whereHas('items', fn ($items) => $items->where('product_id', $product->id))->first();
            return Http::response([
                'id' => 'pi_vendor_product_sale',
                'status' => 'succeeded',
                'amount' => (int) round((float) $order->total * 100),
                'currency' => 'gbp',
                'metadata' => ['order_id' => (string) $order->id],
            ], 200);
        });

        $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
            ->postJson(route('checkout.process'), [
                'name' => $buyer->name,
                'email' => $buyer->email,
                'phone' => '555-0100',
                'address' => '1 Private Customer Street',
                'city' => 'London',
                'postal_code' => 'SW1A 1AA',
                'country' => 'United Kingdom',
                'payment_method' => 'stripe',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');
        $order = Order::firstOrFail();

        $this->postJson(route('checkout.confirm'), [
            'order_id' => $order->id,
            'payment_intent' => 'pi_vendor_product_sale',
        ])->assertOk()->assertJsonPath('status', 'success');

        Mail::assertSent(OrderReceipt::class, fn (OrderReceipt $mail) => $mail->hasTo($buyer->email) && $mail->order->is($order));
        Mail::assertSent(AdminActivityNotification::class, fn (AdminActivityNotification $mail) => $mail->activityTitle === 'Order paid: '.$order->order_number && $mail->hasTo($admin->email));
        Mail::assertSent(MarketplaceSaleNotification::class, function (MarketplaceSaleNotification $mail) use ($vendor, $order, $product) {
            return $mail->hasTo($vendor->email)
                && $mail->orderNumber === $order->order_number
                && str_contains($mail->render(), $product->name)
                && ! str_contains($mail->render(), '1 Private Customer Street');
        });
    });

    $process = $this->postJson(route('checkout.process'), array_merge(checkoutFormData($coupon->code), [
        'subtotal' => 1,
        'discount' => 99,
        'tax_total' => 99,
        'total' => 0.01,
    ]))
        ->assertOk()
        ->assertJsonPath('status', 'success');
    $orderId = $process->json('order_id');
    $order = Order::with('items')->findOrFail($orderId);

    expect((float) $order->subtotal)->toBe(100.0)
        ->and((float) $order->discount)->toBe(10.0)
        ->and((float) $order->tax_total)->toBe(6.75)
        ->and((float) $order->total)->toBe(96.75)
        ->and($order->tax_breakdown)->toHaveCount(2);

    $confirm = ['order_id' => $order->id, 'payment_intent' => 'pi_coupon_test'];
    $this->postJson(route('checkout.confirm'), $confirm)->assertOk()->assertJsonPath('status', 'success');
    $this->postJson(route('checkout.confirm'), $confirm)->assertOk()->assertJsonPath('status', 'success');
    Mail::assertSent(OrderReceipt::class, function (OrderReceipt $mail) use ($order) {
        return $mail->order->is($order)
            && $mail->hasTo('customer@example.test')
            && str_contains($mail->render(), 'Test moisturizer')
            && str_contains($mail->render(), 'Payment is confirmed');
    });
    Mail::assertSent(AdminActivityNotification::class, function (AdminActivityNotification $mail) use ($admin, $order) {
        return $mail->activityTitle === 'Order paid: '.$order->order_number
            && $mail->hasTo($admin->email);
    });
    Mail::assertSent(OrderReceipt::class, 1);
    Mail::assertSent(AdminActivityNotification::class, 1);
    $this->get(route('order.success', $order->order_number))
        ->assertOk()
        ->assertSee('CPN-SAVE-TEN')
        ->assertSee('GST (5%)')
        ->assertSee('Service tax (2.5%)');

    $redemption = CouponRedemption::where('order_id', $order->id)->firstOrFail();
    expect($coupon->fresh()->uses_count)->toBe(1)
        ->and($coupon->fresh()->reserved_count)->toBe(0)
        ->and($redemption->status)->toBe('redeemed')
        ->and($redemption->redeemed_at)->not->toBeNull()
        ->and($order->fresh()->paid_at)->not->toBeNull()
        ->and((int) $product->fresh()->stock)->toBe(8);
});

test('failed payment releases its coupon use exactly once', function () {
    $product = couponTestProduct();
    $coupon = couponTestRecord(['code' => 'CPN-ROLLBACK', 'usage_limit' => 1]);
    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');
    Http::fake(['https://api.stripe.com/*' => Http::response(['error' => ['message' => 'declined']], 500)]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk();
    $this->postJson(route('checkout.process'), checkoutFormData($coupon->code))
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');

    $order = Order::firstOrFail();
    $redemption = CouponRedemption::where('order_id', $order->id)->firstOrFail();
    expect($coupon->fresh()->uses_count)->toBe(0)
        ->and($coupon->fresh()->reserved_count)->toBe(0)
        ->and($order->payment_status)->toBe('failed')
        ->and($redemption->status)->toBe('released');
});

test('a declined Stripe intent releases its reserved coupon use', function () {
    $product = couponTestProduct(price: 20);
    $coupon = couponTestRecord(['code' => 'CPN-CARD-FAIL']);
    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');
    Http::fake(function (ClientRequest $request) {
        if ($request->method() === 'POST') {
            return Http::response(['id' => 'pi_declined', 'client_secret' => 'secret'], 200);
        }

        $order = Order::firstOrFail();
        return Http::response([
            'id' => 'pi_declined',
            'status' => 'requires_payment_method',
            'amount' => (int) round((float) $order->total * 100),
            'currency' => 'gbp',
            'metadata' => ['order_id' => (string) $order->id],
        ], 200);
    });

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk();
    $process = $this->postJson(route('checkout.process'), checkoutFormData($coupon->code))
        ->assertOk();
    $this->postJson(route('checkout.confirm'), [
        'gateway' => 'stripe_failed',
        'order_id' => $process->json('order_id'),
        'payment_intent' => 'pi_declined',
    ])->assertOk()->assertJsonPath('status', 'failed');

    $order = Order::firstOrFail();
    expect($order->payment_status)->toBe('failed')
        ->and($coupon->fresh()->reserved_count)->toBe(0)
        ->and(CouponRedemption::where('order_id', $order->id)->value('status'))->toBe('released');
});

test('malformed Stripe initialization releases its coupon reservation', function () {
    $product = couponTestProduct(price: 20);
    $coupon = couponTestRecord(['code' => 'CPN-INVALID-INTENT']);
    Setting::set('stripe_key', 'pk_test');
    Setting::set('stripe_secret', 'sk_test_secret');
    Http::fake(['https://api.stripe.com/*' => Http::response(['id' => 'pi_no_client_secret'], 200)]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk();
    $this->postJson(route('checkout.process'), checkoutFormData($coupon->code))
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');

    $order = Order::firstOrFail();
    expect($order->payment_status)->toBe('failed')
        ->and($coupon->fresh()->uses_count)->toBe(0)
        ->and($coupon->fresh()->reserved_count)->toBe(0)
        ->and(CouponRedemption::where('order_id', $order->id)->value('status'))->toBe('released');
});

test('incomplete Paystack initialization releases its coupon reservation', function () {
    $product = couponTestProduct(price: 20);
    $coupon = couponTestRecord(['code' => 'CPN-INVALID-PAYSTACK']);
    Setting::set('paystack_key', 'pk_test_public');
    Setting::set('paystack_secret', 'sk_test_secret');
    Http::fake(['https://api.paystack.co/*' => Http::response(['status' => true, 'data' => []], 200)]);

    $this->withSession(['cart' => [$product->id => ['qty' => 1]]])
        ->postJson(route('checkout.coupon'), ['coupon_code' => $coupon->code])
        ->assertOk();
    $this->postJson(route('checkout.process'), checkoutFormData($coupon->code, 'paystack'))
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');

    $order = Order::firstOrFail();
    expect($order->payment_status)->toBe('failed')
        ->and($coupon->fresh()->uses_count)->toBe(0)
        ->and($coupon->fresh()->reserved_count)->toBe(0)
        ->and(CouponRedemption::where('order_id', $order->id)->value('status'))->toBe('released');
});
