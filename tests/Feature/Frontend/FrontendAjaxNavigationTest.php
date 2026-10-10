<?php

test('homepage view more buttons navigate to the shop', function () {
    $shopUrl = route('shop');

    $response = $this->get('/');

    $response->assertOk()->assertSee('View More');
    expect(substr_count($response->getContent(), 'href="'.$shopUrl.'"'))->toBeGreaterThanOrEqual(2);
});

test('contact map is eagerly loaded for ajax page navigation', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertSee('loading="eager"', false);
});
