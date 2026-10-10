<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SupportConversation;
use App\Models\User;

function createPaidOrderItemForSupport(User $user, ?Product $product = null): OrderItem
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.uniqid(),
        'name' => $user->name,
        'email' => $user->email,
        'address' => '10 Test Street',
        'city' => 'London',
        'postal_code' => 'SW1A 1AA',
        'country' => 'United Kingdom',
        'subtotal' => 24,
        'shipping' => 3,
        'total' => 27,
        'payment_method' => 'stripe',
        'payment_status' => 'paid',
        'status' => 'processing',
    ]);

    return $order->items()->create([
        'product_name' => 'Hydrating Lipstick',
        'product_id' => $product?->id,
        'product_price' => 12,
        'qty' => 2,
        'line_total' => 24,
        'emoji' => '💄',
    ]);
}

test('order support link preloads the purchased product context and shows it to admin', function () {
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Lipsticks',
        'slug' => 'lipsticks-'.uniqid(),
    ]);
    $product = Product::create([
        'name' => 'Hydrating Lipstick',
        'slug' => 'hydrating-lipstick-'.uniqid(),
        'category_id' => $category->id,
        'price' => 12,
        'stock' => 20,
    ]);
    ProductImage::create([
        'product_id' => $product->id,
        'path' => 'products/hydrating-lipstick.jpg',
        'sort_order' => 0,
    ]);
    $item = createPaidOrderItemForSupport($user, $product);

    $this->actingAs($user)
        ->get(route('user.support', ['order' => $item->order_id, 'item' => $item->id]))
        ->assertOk()
        ->assertSee('Product: Hydrating Lipstick')
        ->assertSee('Order number: '.$item->order->order_number)
        ->assertSee('Quantity: 2')
        ->assertSee('products/hydrating-lipstick.jpg')
        ->assertSee('support-request-context', false)
        ->assertSee('data-preview-url="'.asset('storage/products/hydrating-lipstick.jpg').'"', false)
        ->assertSee('Product photo and order details will accompany your message.')
        ->assertSee('data-order-item="'.$item->id.'"', false);

    $this->actingAs($user)
        ->postJson(route('user.support.send'), [
            'body' => 'My lipstick has not arrived.',
            'order_item_id' => $item->id,
        ])
        ->assertCreated()
        ->assertJsonPath('message.context.product_name', 'Hydrating Lipstick')
        ->assertJsonPath('message.context.quantity', 2)
        ->assertJsonPath('message.context.line_total', '24.00')
        ->assertJsonPath('message.context.order_number', $item->order->order_number)
        ->assertJsonPath('message.context.image_url', asset('storage/products/hydrating-lipstick.jpg'));

    $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();
    $this->actingAs($admin)
        ->getJson(route('admin.support.messages', $conversation))
        ->assertOk()
        ->assertJsonPath('messages.0.context.product_name', 'Hydrating Lipstick')
        ->assertJsonPath('messages.0.body', 'My lipstick has not arrived.');
});

test('customers cannot attach another customer order item to a support message', function () {
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $otherItem = createPaidOrderItemForSupport($otherUser);

    $this->actingAs($user)
        ->postJson(route('user.support.send'), [
            'body' => 'Please check this product.',
            'order_item_id' => $otherItem->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('order_item_id');
});

test('support page shows every product image in the selected order', function () {
    $user = User::factory()->create(['role' => 'user']);
    $category = Category::create([
        'name' => 'Skincare',
        'slug' => 'skincare-'.uniqid(),
    ]);
    $firstProduct = Product::create([
        'name' => 'Daily Moisturizer',
        'slug' => 'daily-moisturizer-'.uniqid(),
        'category_id' => $category->id,
        'price' => 12,
        'stock' => 20,
    ]);
    $secondProduct = Product::create([
        'name' => 'Night Serum',
        'slug' => 'night-serum-'.uniqid(),
        'category_id' => $category->id,
        'price' => 18,
        'stock' => 20,
    ]);
    ProductImage::create([
        'product_id' => $firstProduct->id,
        'path' => 'products/moisturizer.jpg',
        'sort_order' => 0,
    ]);
    ProductImage::create([
        'product_id' => $secondProduct->id,
        'path' => 'products/night-serum.jpg',
        'sort_order' => 0,
    ]);
    $firstItem = createPaidOrderItemForSupport($user, $firstProduct);
    $secondItem = $firstItem->order->items()->create([
        'product_name' => 'Night Serum',
        'product_id' => $secondProduct->id,
        'product_price' => 18,
        'qty' => 1,
        'line_total' => 18,
        'emoji' => '🧴',
    ]);

    $this->actingAs($user)
        ->get(route('user.support', ['order' => $firstItem->order_id, 'item' => $firstItem->id]))
        ->assertOk()
        ->assertSee('products/moisturizer.jpg')
        ->assertSee('products/night-serum.jpg')
        ->assertSee('Hydrating Lipstick')
        ->assertSee('Night Serum');

    $this->actingAs($user)
        ->postJson(route('user.support.send'), [
            'body' => 'Please help with these products.',
            'order_item_id' => $firstItem->id,
        ])
        ->assertCreated()
        ->assertJsonPath('message.context.items.0.product_name', 'Hydrating Lipstick')
        ->assertJsonPath('message.context.items.1.product_name', 'Night Serum')
        ->assertJsonPath('message.context.items.1.image_url', asset('storage/products/night-serum.jpg'));

    expect($secondItem->exists)->toBeTrue();
});

test('order history provides a dedicated product return tab', function () {
    $user = User::factory()->create(['role' => 'user']);
    $item = createPaidOrderItemForSupport($user);
    $item->order->update(['status' => 'delivered']);

    $this->actingAs($user)
        ->get(route('user.orders'))
        ->assertOk()
        ->assertSee('Order invoice')
        ->assertSee('id="invoice-'.$item->order_id.'"', false)
        ->assertSee('Hydrating Lipstick')
        ->assertSee('href="'.route('user.returns', ['order' => $item->order_id]).'#return-order-'.$item->order_id.'">', false)
        ->assertSee('Return the product')
        ->assertSee('Need Help?')
        ->assertSee('Need help with this order?')
        ->assertSee('data-order-item="'.$item->id.'"', false)
        ->assertSee(route('user.support', ['order' => $item->order_id, 'item' => $item->id]))
        ->assertSee('Request or track a return');
});
