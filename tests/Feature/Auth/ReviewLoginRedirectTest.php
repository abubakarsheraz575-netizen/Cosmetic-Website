<?php

use App\Models\User;

test('review login redirect preserves the product page and reviews fragment', function () {
    $destination = '/product/embark-dcaa1#reviews';

    $this->get('/login-redirect?redirect=' . urlencode($destination))
        ->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(url('/product/embark-dcaa1#reviews'));
});

test('review login redirect does not accept an external destination', function () {
    $this->get('/login-redirect?redirect=' . urlencode('https://example.org/product/embark-dcaa1#reviews'))
        ->assertRedirect(route('login'))
        ->assertSessionMissing('url.intended');
});

test('ajax login returns the safe intended product review URL', function () {
    $user = User::factory()->create();
    $destination = url('/product/embark-dcaa1#reviews');

    $this->withSession(['url.intended' => $destination])
        ->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertOk()
        ->assertJsonPath('redirect', $destination);

    $this->assertAuthenticatedAs($user);
});
