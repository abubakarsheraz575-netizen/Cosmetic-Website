<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('admin reviews identify the customer and show the reviewed product image and details', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Mina Customer',
        'email' => 'mina@example.test',
    ]);
    $category = Category::create(['name' => 'Face care', 'slug' => 'face-care']);
    $product = Product::create([
        'name' => 'Daily Glow Serum',
        'slug' => 'daily-glow-serum',
        'category_id' => $category->id,
        'price' => 24,
        'discount' => 10,
        'stock' => 7,
        'emoji' => '✨',
        'is_active' => true,
        'review_status' => 'approved',
    ]);
    Storage::disk('public')->put('products/glow.png', 'product-image');
    ProductImage::create(['product_id' => $product->id, 'path' => 'products/glow.png', 'sort_order' => 0]);
    Review::create([
        'product_id' => $product->id,
        'user_id' => $customer->id,
        'name' => $customer->name,
        'rating' => 5,
        'comment' => 'My skin feels wonderful.',
        'status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get('/admin/reviews')
        ->assertOk()
        ->assertSee('Mina Customer')
        ->assertSee('mina@example.test')
        ->assertSee('Customer account #'.$customer->id)
        ->assertDontSee('Guest review · no linked customer account')
        ->assertSee('Daily Glow Serum')
        ->assertSee('Face care')
        ->assertSee('SKU GL-'.str_pad($product->id, 4, '0', STR_PAD_LEFT))
        ->assertSee('£21.60')
        ->assertSee(asset('storage/products/glow.png'), false);
});
