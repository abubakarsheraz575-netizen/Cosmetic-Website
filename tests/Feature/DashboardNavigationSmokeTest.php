<?php

use App\Models\User;

test('admin dashboard navigation pages render for an administrator', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $paths = [
        '/admin/dashboard',
        '/admin/users',
        '/admin/categories',
        '/admin/sizes',
        '/admin/products',
        '/admin/products/create',
        '/admin/product-submissions',
        '/admin/announcements',
        '/admin/orders',
        '/admin/reviews',
        '/admin/shipping',
        '/admin/mail-settings',
        '/admin/payment-settings',
        '/admin/store-settings',
        '/admin/about-settings',
        '/admin/page-headers',
        '/admin/profile',
        '/admin/support',
        '/admin/contacts',
        '/admin/announcements/manage',
    ];

    foreach ($paths as $path) {
        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertSee('.main :is(img, svg, video, canvas)', false);
    }

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('@media (max-width: 480px)', false)
        ->assertSee('padding-left: 12px !important', false);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('background-position: right 14px center', false)
        ->assertSee('appearance: none', false);

    $this->actingAs($admin)
        ->get('/admin/products/create')
        ->assertOk()
        ->assertSee('id="productsToggle"', false)
        ->assertSee('aria-expanded="true"', false)
        ->assertSee('aria-controls="productsSub"', false)
        ->assertSee('id="productsSub"', false)
        ->assertSee('class="k-sub-link k-sub-active"', false);
});

test('admin navbar controls are initialized alongside the shared ajax navigator', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('id="menuBtn"', false)
        ->assertSee('id="themeBtn"', false)
        ->assertSee("document.addEventListener('click', function(e) {", false)
        ->assertDontSee("if (!window.__ajaxNavigationRouter) document.addEventListener('click', function(e) {", false);
});

test('announcement creation and recipient pages render for an administrator', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/announcements')
        ->assertOk()
        ->assertSee('Manage announcements')
        ->assertSee('value="warning"', false)
        ->assertSee('value="success"', false)
        ->assertDontSee('id="announcementRows"', false);
});

test('user dashboard navigation pages render for a verified customer', function () {
    $user = User::factory()->create(['role' => 'user']);
    $paths = [
        '/user/dashboard',
        '/user/orders',
        '/user/wishlist',
        '/user/dashboard/products',
        '/user/dashboard/products/submissions',
        '/user/announcements',
        '/user/profile',
        '/user/support',
    ];

    foreach ($paths as $path) {
        $this->actingAs($user)
            ->get($path)
            ->assertOk()
            ->assertSee('.layout > .main', false);
    }

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk()
        ->assertSee('width: 100%', false)
        ->assertDontSee('width: 0%', false);
});
