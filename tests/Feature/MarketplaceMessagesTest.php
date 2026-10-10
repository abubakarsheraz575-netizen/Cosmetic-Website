<?php

use App\Models\Category;
use App\Models\MarketplaceMessage;
use App\Models\Product;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use Illuminate\Support\Facades\Mail;

test('vendor and admin exchange messages in the same vendor-only conversation', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $vendor = User::factory()->create(['role' => 'user']);
    $category = Category::create(['name' => 'Vendor Test', 'slug' => 'vendor-test']);
    Product::create([
        'name' => 'Vendor test product',
        'slug' => 'vendor-test-product',
        'category_id' => $category->id,
        'price' => 15,
        'discount' => 0,
        'stock' => 4,
        'is_active' => false,
        'submitted_by' => $vendor->id,
        'review_status' => 'pending',
    ]);

    $adminReply = $this->actingAs($admin)->postJson(route('admin.marketplace.messages.send'), [
        'vendor_id' => $vendor->id,
        'body' => 'Your submission is being reviewed.',
    ])->assertCreated()->assertJsonPath('message.mine', true);
    $adminMessageId = $adminReply->json('message.id');

    $this->actingAs($vendor)->getJson(route('user.marketplace.messages', ['after' => 0]))
        ->assertOk()
        ->assertJsonPath('messages.0.body', 'Your submission is being reviewed.')
        ->assertJsonPath('messages.0.mine', false);
    expect(MarketplaceMessage::findOrFail($adminMessageId)->read_at)->not->toBeNull();

    $vendorReply = $this->actingAs($vendor)->postJson(route('user.marketplace.messages.send'), [
        'body' => 'Thank you for the update.',
    ])->assertCreated()->assertJsonPath('message.mine', true)
        ->assertJsonPath('admin_notified', true);
    Mail::assertSent(AdminActivityNotification::class, function (AdminActivityNotification $mail) use ($admin, $vendor) {
        return $mail->activityTitle === 'New marketplace message from a vendor'
            && $mail->hasTo($admin->email)
            && str_contains($mail->details['Vendor'], $vendor->email);
    });
    $vendorMessageId = $vendorReply->json('message.id');

    $this->actingAs($admin)->getJson(route('admin.marketplace.messages.feed', [
        'vendor' => $vendor->id,
        'after' => $adminMessageId,
    ]))->assertOk()
        ->assertJsonPath('messages.0.body', 'Thank you for the update.')
        ->assertJsonPath('messages.0.mine', false);
    expect(MarketplaceMessage::findOrFail($vendorMessageId)->read_at)->not->toBeNull();

    $otherVendor = User::factory()->create(['role' => 'user']);
    $this->actingAs($otherVendor)->getJson(route('user.marketplace.messages', ['after' => 0]))->assertNotFound();
});

test('vendor withdrawals have a separate page and remain private to their owner', function () {
    $vendor = User::factory()->create(['role' => 'user']);
    $category = Category::create(['name' => 'Withdrawal Test', 'slug' => 'withdrawal-test']);
    Product::create([
        'name' => 'Withdrawal vendor product',
        'slug' => 'withdrawal-vendor-product',
        'category_id' => $category->id,
        'price' => 15,
        'discount' => 0,
        'stock' => 4,
        'is_active' => false,
        'submitted_by' => $vendor->id,
        'review_status' => 'pending',
    ]);

    $this->actingAs($vendor)
        ->get(route('user.marketplace'))
        ->assertOk()
        ->assertSee('Marketplace messages')
        ->assertDontSee('Request a withdrawal')
        ->assertSee(route('user.marketplace.withdrawals'));

    $this->actingAs($vendor)
        ->get(route('user.marketplace.withdrawals'))
        ->assertOk()
        ->assertSee('Request payment')
        ->assertSee('Your paid product sales')
        ->assertSee('Request history')
        ->assertSee('No paid marketplace sales yet');

    $this->actingAs($vendor)
        ->post(route('user.marketplace.withdrawals.store'), [
            'order_id' => 987654,
            'payout_method' => 'PayPal',
            'payout_details' => 'vendor@example.test',
        ])
        ->assertSessionHasErrors('order_id');
    expect(\App\Models\MarketplaceWithdrawal::count())->toBe(0);

    $otherVendor = User::factory()->create(['role' => 'user']);
    $this->actingAs($otherVendor)->get(route('user.marketplace.withdrawals'))->assertNotFound();
});
