<?php

use App\Models\Category;
use App\Models\Size;
use App\Models\User;

test('category and size listings render interactive status controls', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/categories')
        ->assertOk()
        ->assertSee('onclick="catToggleStatus(this)"', false)
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('id="catSearch"', false);

    $this->actingAs($admin)
        ->get('/admin/sizes')
        ->assertOk()
        ->assertSee('onclick="szToggleStatus(this)"', false)
        ->assertSee('data-server-pagination="true"', false)
        ->assertSee('id="szSearch"', false);
});

test('an admin can change a category status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Status Test Category',
        'slug' => 'status-test-category',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patchJson("/admin/categories/{$category->id}/status", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('category.id', $category->id)
        ->assertJsonPath('category.is_active', false);

    expect($category->fresh()->is_active)->toBeFalse();
});

test('category status changes require a boolean value', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create([
        'name' => 'Invalid Category Status',
        'slug' => 'invalid-category-status',
    ]);

    $this->actingAs($admin)
        ->patchJson("/admin/categories/{$category->id}/status", ['is_active' => 'invalid'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('is_active');

    expect($category->fresh()->is_active)->toBeTrue();
});

test('an admin can change a size status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $size = Size::create(['name' => 'Status Test Size', 'ml' => 75, 'is_active' => true]);

    $this->actingAs($admin)
        ->patchJson("/admin/sizes/{$size->id}/status", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('size.id', $size->id)
        ->assertJsonPath('size.is_active', false);

    expect($size->fresh()->is_active)->toBeFalse();
});

test('size status changes require a boolean value', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $size = Size::create(['name' => 'Invalid Size Status', 'ml' => 125]);

    $this->actingAs($admin)
        ->patchJson("/admin/sizes/{$size->id}/status", ['is_active' => 'invalid'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('is_active');

    expect($size->fresh()->is_active)->toBeTrue();
});
