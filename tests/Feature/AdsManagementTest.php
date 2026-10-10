<?php

use App\Models\Ad;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('admins can create and update scheduled ads with secure external urls', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.ads.store'), [
        'name' => 'Summer glow',
        'title' => 'A fresh summer edit',
        'subtitle' => 'Lightweight daily essentials',
        'button_label' => 'Shop the edit',
        'target_url' => 'https://example.com/shop',
        'image_url' => 'https://images.example.com/summer.jpg',
        'tablet_image_url' => 'https://images.example.com/summer-mobile.jpg',
        'status' => 'published',
        'sort_order' => 1,
        'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
        'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
    ])->assertRedirect(route('admin.ads.index'));

    $ad = Ad::firstOrFail();
    expect($ad->shortcode)->not->toBeEmpty()
        ->and($ad->created_by)->toBe($admin->id);

    $this->actingAs($admin)->patch(route('admin.ads.update', $ad), [
        'name' => 'Summer glow',
        'title' => 'The summer edit',
        'target_url' => 'https://example.com/summer',
        'image_url' => 'https://images.example.com/summer.jpg',
        'status' => 'draft',
        'sort_order' => 2,
    ])->assertRedirect(route('admin.ads.index'));

    expect($ad->fresh()->title)->toBe('The summer edit')
        ->and($ad->fresh()->status)->toBe('draft');

    $this->actingAs($admin)->post(route('admin.ads.store'), [
        'name' => 'Insecure image',
        'title' => 'Not allowed',
        'target_url' => 'https://example.com',
        'image_url' => 'http://images.example.com/image.jpg',
        'status' => 'published',
        'sort_order' => 0,
    ])->assertSessionHasErrors('image_url');

    $this->actingAs($admin)->post(route('admin.ads.store'), [
        'name' => 'Insecure destination',
        'title' => 'Not allowed',
        'target_url' => 'http://example.com',
        'image_url' => 'https://images.example.com/image.jpg',
        'status' => 'published',
        'sort_order' => 0,
    ])->assertSessionHasErrors('target_url');
});

test('only enabled published ads within their schedule appear on the homepage and clicks are counted', function () {
    Setting::set('ads_enabled', '1');
    $live = Ad::create([
        'name' => 'Live banner', 'shortcode' => 'LIVE-BANNER', 'title' => 'A live promotion',
        'target_url' => 'https://example.com/collection', 'image_url' => 'https://images.example.com/live.jpg',
        'status' => 'published', 'starts_at' => now()->subMinute(), 'expires_at' => now()->addDay(),
    ]);
    Ad::create([
        'name' => 'Draft banner', 'shortcode' => 'DRAFT-BANNER', 'title' => 'A hidden promotion',
        'target_url' => 'https://example.com', 'image_url' => 'https://images.example.com/draft.jpg',
        'status' => 'draft',
    ]);
    Ad::create([
        'name' => 'Expired banner', 'shortcode' => 'EXPIRED-BANNER', 'title' => 'An expired promotion',
        'target_url' => 'https://example.com', 'image_url' => 'https://images.example.com/expired.jpg',
        'status' => 'published', 'expires_at' => now()->subMinute(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('A live promotion')
        ->assertDontSee('A hidden promotion')
        ->assertDontSee('An expired promotion');

    $this->get(route('ads.click', $live))
        ->assertRedirect('https://example.com/collection');
    expect($live->fresh()->clicks_count)->toBe(1);

    $local = Ad::create([
        'name' => 'Shop banner', 'shortcode' => 'SHOP-BANNER', 'title' => 'Visit the shop',
        'target_url' => '/shop', 'image_url' => 'https://images.example.com/shop.jpg',
        'status' => 'published',
    ]);
    $this->get(route('ads.click', $local))
        ->assertRedirect(route('shop'));
    expect($local->fresh()->clicks_count)->toBe(1);

    Setting::set('ads_txt_content', "example.com, pub-123, DIRECT");
    $this->get(route('ads.txt'))->assertOk()->assertSeeText('example.com, pub-123, DIRECT');
});

test('admins can use media library images in dynamic ads and referenced images are retained', function () {
    Storage::fake('public');
    Storage::disk('public')->put('campaigns/glow.jpg', 'image bytes');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.ads.store'), [
        'name' => 'Media library campaign',
        'title' => 'Discover the glow edit',
        'target_url' => '/shop',
        'image_url' => 'campaigns/glow.jpg',
        'tablet_image_url' => 'campaigns/glow.jpg',
        'status' => 'published',
        'sort_order' => 0,
    ])->assertRedirect(route('admin.ads.index'));

    $ad = Ad::firstOrFail();
    expect($ad->imageSource('image_url'))->toBe('/storage/campaigns/glow.jpg')
        ->and($ad->imageSource('tablet_image_url'))->toBe('/storage/campaigns/glow.jpg');

    Setting::set('ads_enabled', '1');
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/storage/campaigns/glow.jpg');
    $this->get(route('admin.media.picker'))
        ->assertOk()
        ->assertJsonPath('images.0.path', 'campaigns/glow.jpg');

    $this->actingAs($admin)->patch(route('admin.ads.update', $ad), [
        'name' => 'Media library campaign',
        'title' => 'Updated glow edit',
        'target_url' => '/shop',
        'image_url' => 'campaigns/glow.jpg',
        'tablet_image_url' => 'campaigns/glow.jpg',
        'status' => 'published',
        'sort_order' => 0,
    ])->assertRedirect(route('admin.ads.index'));
    expect($ad->fresh()->imageSource('image_url'))->toBe('/storage/campaigns/glow.jpg')
        ->and($ad->fresh()->title)->toBe('Updated glow edit');

    \App\Support\MediaLibrary::deleteIfUnused('campaigns/glow.jpg');
    Storage::disk('public')->assertExists('campaigns/glow.jpg');

    $this->actingAs($admin)->post(route('admin.ads.store'), [
        'name' => 'Missing library image',
        'title' => 'Invalid image',
        'target_url' => '/shop',
        'image_url' => 'campaigns/not-uploaded.jpg',
        'status' => 'draft',
        'sort_order' => 1,
    ])->assertSessionHasErrors('image_url');
});
