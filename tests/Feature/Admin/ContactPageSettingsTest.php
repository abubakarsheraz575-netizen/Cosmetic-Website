<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('contact page copy and in-page photos are editable from store settings', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=');

    $this->actingAs($admin)
        ->get('/admin/store-settings')
        ->assertOk()
        ->assertSee('Contact Page Content & Photos')
        ->assertSee('name="contact_image"', false);

    $this->post('/admin/store-settings', [
        '_method' => 'PATCH',
        'contact_intro_label' => 'Our beauty team',
        'contact_intro_text' => 'We are here for your questions.',
        'contact_form_tag' => 'Write to us',
        'contact_form_title' => 'How can we help?',
        'contact_info_title' => 'Talk with our team',
        'contact_info_text' => 'Product and order support is available.',
        'contact_image_alt' => 'Our skincare collection',
        'footer_tagline' => 'Beauty, your way.',
        'footer_copyright' => '© {year} Glow Lab',
        'footer_link_1_label' => 'New arrivals',
        'footer_link_1_url' => '/shop?sort=latest',
        'contact_image' => UploadedFile::fake()->createWithContent('contact.png', $png),
    ], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $path = Setting::get('contact_image');
    Storage::disk('public')->assertExists($path);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('Our beauty team')
        ->assertSee('We are here for your questions.')
        ->assertSee('How can we help?')
        ->assertSee('Talk with our team')
        ->assertSee('Beauty, your way.')
        ->assertSee('© '.date('Y').' Glow Lab')
        ->assertSee('New arrivals')
        ->assertSee('href="/shop?sort=latest"', false)
        ->assertSee('Our skincare collection')
        ->assertSee(asset('storage/' . $path), false);
});

test('footer has a dedicated admin page and saves only footer settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Setting::set('site_phone', '555-0100');

    $this->actingAs($admin)
        ->get('/admin/footer-settings')
        ->assertOk()
        ->assertSee('Footer settings')
        ->assertSee('Save footer settings')
        ->assertSee('name="footer_tagline"', false);

    $this->patch('/admin/footer-settings', [
        'footer_contact_title' => 'Reach our team',
        'footer_links_title' => 'Explore',
        'footer_tagline' => 'Care for every day.',
        'footer_copyright' => '© {year} Radiant',
        'footer_link_1_label' => 'Shop now',
        'footer_link_1_url' => '/shop',
        'footer_link_2_label' => 'Journal',
        'footer_link_2_url' => '/blog',
        'footer_link_3_label' => 'Help',
        'footer_link_3_url' => '/faqs',
        'footer_link_4_label' => 'About',
        'footer_link_4_url' => '/about',
        'footer_link_5_label' => 'Reviews',
        'footer_link_5_url' => '/testimonials',
    ])->assertRedirect(route('admin.footer-settings'));

    expect(Setting::get('footer_tagline'))->toBe('Care for every day.')
        ->and(Setting::get('footer_link_1_label'))->toBe('Shop now')
        ->and(Setting::get('site_phone'))->toBe('555-0100');

    $this->patch('/admin/footer-settings', [
        'footer_link_1_url' => 'javascript:alert(1)',
    ])->assertSessionHasErrors('footer_link_1_url');
});
