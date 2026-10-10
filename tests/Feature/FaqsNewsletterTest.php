<?php

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use Illuminate\Support\Facades\Mail;

test('public FAQ page displays published questions and category details', function () {
    $category = FaqCategory::where('slug', 'products-care')->firstOrFail();
    $faq = Faq::create([
        'faq_category_id' => $category->id,
        'question' => 'How do I keep my routine simple?',
        'answer' => 'Start with the directions on the products you already use.',
        'status' => 'published',
        'sort_order' => 1,
    ]);
    Faq::create([
        'faq_category_id' => $category->id,
        'question' => 'Hidden draft question',
        'answer' => 'Not public yet.',
        'status' => 'draft',
    ]);

    $this->get(route('faqs.index'))
        ->assertOk()
        ->assertSee($faq->question)
        ->assertDontSee('Hidden draft question')
        ->assertSee(route('faqs.category', $category->slug), false);
    $this->get(route('faqs.category', $category->slug))
        ->assertOk()
        ->assertSee($faq->answer)
        ->assertDontSee('Hidden draft question');
});

test('admins can create edit and delete FAQs and categories', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = FaqCategory::firstOrFail();

    $this->actingAs($admin)->get(route('admin.faqs.index'))
        ->assertOk()->assertSee('Frequently asked questions')->assertSee('data-confirm-modal', false);
    $this->get(route('admin.faqs.categories'))->assertOk()->assertSee('FAQ categories')
        ->assertSee('Add category')->assertDontSee('Create category</h2>', false);
    $this->get(route('admin.faqs.categories.create'))->assertOk()->assertSee('Add a category');
    $this->get(route('admin.faqs.categories.edit', $category))->assertOk()->assertSee('Edit category');
    $this->get(route('admin.faqs.categories.show', $category))
        ->assertOk()->assertSee('Which payment methods can I use?');

    $this->post(route('admin.faqs.store'), [
        'faq_category_id' => $category->id,
        'question' => 'Can I track a gift order?',
        'answer' => 'Use the order status available in your account.',
        'source_url' => 'https://www.fda.gov/cosmetics',
        'status' => 'published',
        'sort_order' => 10,
    ])->assertRedirect(route('admin.faqs.index'));
    $faq = Faq::where('question', 'Can I track a gift order?')->firstOrFail();
    $this->patch(route('admin.faqs.update', $faq), [
        'faq_category_id' => $category->id,
        'question' => 'Can I track my gift order?',
        'answer' => 'Yes. Use the order status available in your account.',
        'status' => 'draft',
        'sort_order' => 10,
    ])->assertRedirect(route('admin.faqs.index'));
    expect($faq->fresh()->question)->toBe('Can I track my gift order?')
        ->and($faq->fresh()->status)->toBe('draft');
    $this->delete(route('admin.faqs.destroy', $faq))->assertRedirect();

    $this->post(route('admin.faqs.categories.store'), [
        'name' => 'Beauty routines',
        'description' => 'Tips for everyday routines.',
        'sort_order' => 5,
    ])->assertRedirect();
    $newCategory = FaqCategory::where('slug', 'beauty-routines')->firstOrFail();
    $this->patch(route('admin.faqs.categories.update', $newCategory), [
        'name' => 'Daily beauty routines',
        'description' => 'Simple ideas for everyday routines.',
        'sort_order' => 6,
        'is_active' => '1',
    ])->assertRedirect();
    expect($newCategory->fresh()->slug)->toBe('daily-beauty-routines');
    $this->delete(route('admin.faqs.categories.destroy', $newCategory))->assertRedirect();
});

test('newsletter signup requires consent, stores a normalized email, and admin can manage subscribers', function () {
    Mail::fake();

    $this->postJson(route('newsletter.subscribe'), [
        'email' => '   ',
        'consent' => '1',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
    $this->postJson(route('newsletter.subscribe'), [
        'email' => 'BEAUTY@example.com',
        'source' => 'about',
        'consent' => '1',
    ])->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('admin_notified', true);
    Mail::assertSent(AdminActivityNotification::class, function (AdminActivityNotification $mail) {
        return $mail->activityTitle === 'New newsletter subscriber'
            && $mail->hasTo(config('mail.from.address'))
            && in_array('beauty@example.com', $mail->details, true);
    });
    $this->postJson(route('newsletter.subscribe'), [
        'email' => 'beauty@example.com',
        'source' => 'home',
        'consent' => '1',
    ])->assertOk();
    expect(NewsletterSubscriber::count())->toBe(1);
    $subscriber = NewsletterSubscriber::firstOrFail();
    expect($subscriber->email)->toBe('beauty@example.com')
        ->and($subscriber->source)->toBe('home');

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('admin.newsletters.index'))
        ->assertOk()->assertSee('beauty@example.com')->assertSee('data-confirm-modal', false);
    $this->patch(route('admin.newsletters.status', $subscriber), ['status' => 'unsubscribed'])
        ->assertRedirect();
    expect($subscriber->fresh()->status)->toBe('unsubscribed');

    $this->postJson(route('newsletter.subscribe'), [
        'email' => 'beauty@example.com',
        'consent' => '1',
    ])->assertOk();
    expect($subscriber->fresh()->status)->toBe('subscribed');

    $this->delete(route('admin.newsletters.destroy', $subscriber))->assertRedirect();
    $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
});
