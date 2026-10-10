<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('beauty profile starts at zero and does not show placeholder preferences', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk()
        ->assertSee('<b id="ringNum">0%</b>', false)
        ->assertSee('Your <strong>Beauty Profile</strong> is currently <strong>0% complete</strong>', false)
        ->assertSee('class="bp-completion-note"', false)
        ->assertSee('id="beautyCompletionNote" role="status"', false)
        ->assertSee('Not set')
        ->assertSee('id="beautyForm"', false)
        ->assertSee('id="editBeautyBtn"', false)
        ->assertSee('class="bp-modal-card"', false)
        ->assertSee('class="bp-edit"', false)
        ->assertSee('align-items: stretch', false)
        ->assertSee('background-position: right 14px center', false);

    $this->actingAs($user)
        ->get('/user/profile')
        ->assertOk()
        ->assertSee('0%')
        ->assertSee('Choose skin type')
        ->assertDontSee('Warm Beige')
        ->assertSee('align-items: stretch', false)
        ->assertSee('background-position: right 15px center', false);
});

test('beauty profile preferences can be saved partially or cleared', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->postJson('/user/profile/prefs', [
            '_method' => 'PATCH',
            'skin_type' => 'Sensitive',
            'hair_type' => 'Curly',
            'shade_match' => 'Warm Beige',
            'loves' => 'Skincare',
        ])
        ->assertOk();

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk()
        ->assertSee('<b id="ringNum">100%</b>', false)
        ->assertSee('id="beautyCompletionNote" role="status" hidden', false);

    $this->actingAs($user)
        ->postJson('/user/profile/prefs', [
            '_method' => 'PATCH',
            'skin_type' => 'Sensitive',
            'hair_type' => null,
            'shade_match' => '',
            'loves' => 'Skincare',
        ])
        ->assertOk()
        ->assertJsonPath('user.skin_type', 'Sensitive')
        ->assertJsonPath('user.hair_type', null)
        ->assertJsonPath('user.shade_match', null)
        ->assertJsonPath('user.loves', 'Skincare');

    expect($user->fresh()->skin_type)->toBe('Sensitive')
        ->and($user->fresh()->hair_type)->toBeNull()
        ->and($user->fresh()->shade_match)->toBeNull()
        ->and($user->fresh()->loves)->toBe('Skincare');

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk()
        ->assertSee('<b id="ringNum">50%</b>', false);

    $this->actingAs($user)
        ->postJson('/user/profile/prefs', [
            '_method' => 'PATCH',
            'skin_type' => null,
            'hair_type' => null,
            'shade_match' => null,
            'loves' => null,
        ])
        ->assertOk();

    expect($user->fresh()->skin_type)->toBeNull()
        ->and($user->fresh()->loves)->toBeNull();

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk()
        ->assertSee('<b id="ringNum">0%</b>', false);
});

test('wishlist product image has a valid add-to-bag click handler', function () {
    $user = User::factory()->create(['role' => 'user']);
    $category = Category::create(['name' => 'Wishlist Beauty', 'slug' => 'wishlist-beauty']);
    $size = Size::create(['name' => 'Wishlist Size', 'ml' => 30]);
    $product = Product::create([
        'name' => 'Wishlist Product',
        'slug' => 'wishlist-product',
        'category_id' => $category->id,
        'size_id' => $size->id,
        'price' => 25,
        'stock' => 3,
    ]);
    DB::table('wishlists')->insert([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/user/wishlist')
        ->assertOk()
        ->assertSee('onclick="addToBag('.$product->id.', 1, event, { open: true })"', false)
        ->assertSee('id="drawerRoot"', false);
});
