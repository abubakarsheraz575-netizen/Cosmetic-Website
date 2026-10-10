<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('about settings control every editorial section and preserve uploaded images', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=');

    $this->actingAs($admin)
        ->get('/admin/about-settings')
        ->assertOk()
        ->assertSee('Products section')
        ->assertSee('About statistics')
        ->assertSee('Quote banner')
        ->assertSee('Store highlights')
        ->assertSee('Visible About page sections')
        ->assertSee('id="aboutQuoteImage"', false);

    $this->postJson('/admin/about-settings', [
        'about_tag' => 'A considered beauty routine',
        'about_title' => 'Our story grows with you',
        'about_text' => "First new paragraph.\nSecond new paragraph.",
        'about_services' => "Cleanser\nMoisturizer",
        'about_services_label' => 'What we make',
        'about_products_tagline' => 'Made for your routine',
        'about_products_title' => 'The collection',
        'about_products_text' => 'A live collection of available products.',
        'about_products_button' => 'Browse the range',
        'about_product_cta' => 'Discover this item',
        'about_products_empty_title' => 'Collection arriving soon',
        'about_products_empty_text' => 'Please check back shortly.',
        'about_quote' => "Beauty begins with care.\nEvery day.",
        'about_quote_author' => '— Our team',
        'about_feature_1_title' => 'Thoughtful offers',
        'about_feature_1_text' => 'Offers selected with care.',
        'about_feature_2_title' => 'Careful delivery',
        'about_feature_2_text' => 'Packed and shipped thoughtfully.',
        'about_feature_3_title' => 'Easy returns',
        'about_feature_3_text' => 'We want you to feel confident.',
        'stat_years' => '12',
        'stat_clients' => '30',
        'stat_products' => '145',
        'stat_satisfaction' => '99',
        'about_stat_years_suffix' => ' years',
        'about_stat_years_label' => 'Years caring for customers',
        'about_stat_clients_suffix' => 'k+',
        'about_stat_clients_label' => 'Happy customers',
        'about_stat_products_suffix' => ' curated',
        'about_stat_products_label' => 'Beauty essentials',
        'about_stat_satisfaction_suffix' => '%',
        'about_stat_satisfaction_label' => 'Customer satisfaction',
        'about_show_story' => '1',
        'about_show_products' => '1',
        'about_show_stats' => '1',
        'about_show_quote' => '1',
        'about_show_features' => '1',
        'about_show_categories' => '0',
        'about_image' => UploadedFile::fake()->createWithContent('about.png', $png),
        'about_quote_image' => UploadedFile::fake()->createWithContent('quote.png', $png),
    ])->assertOk()->assertJsonPath('status', 'success');

    Storage::disk('public')->assertExists(Setting::get('about_image'));
    Storage::disk('public')->assertExists(Setting::get('about_quote_image'));
    expect(Setting::get('stat_years'))->toBe('12')
        ->and(Setting::get('about_stat_clients_label'))->toBe('Happy customers');

    $this->get('/about')
        ->assertOk()
        ->assertSee('A considered beauty routine')
        ->assertSee('Our story grows with you')
        ->assertSee('What we make')
        ->assertSee('<p>First new paragraph.</p>', false)
        ->assertSee('<p>Second new paragraph.</p>', false)
        ->assertSee('Made for your routine')
        ->assertSee('The collection')
        ->assertSee('Beauty begins with care.')
        ->assertSee('— Our team')
        ->assertSee('Thoughtful offers')
        ->assertSee('Years caring for customers')
        ->assertDontSee('LOGOIPSUM')
        ->assertDontSee('class="brands-sec"', false);

    $this->postJson('/admin/about-settings', [
        'about_show_story' => '0',
        'about_show_products' => '0',
        'about_show_stats' => '0',
        'about_show_quote' => '0',
        'about_show_features' => '0',
        'about_show_categories' => '0',
    ])->assertOk();

    $this->get('/about')
        ->assertOk()
        ->assertDontSee('Our story grows with you')
        ->assertDontSee('The collection')
        ->assertDontSee('Beauty begins with care.')
        ->assertDontSee('Thoughtful offers')
        ->assertDontSee('Years caring for customers');
});
