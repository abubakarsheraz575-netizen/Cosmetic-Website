<?php

use App\Mail\ContactMessageReceived;
use App\Mail\ContactMessageReply;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('public contact form stores the message and confirms receipt', function () {
    Mail::fake();

    $this->post(route('contact.send'), [
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'subject' => 'Order question',
        'message' => 'Could you help me with my order?',
    ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('contact_success');

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'subject' => 'Order question',
        'message' => 'Could you help me with my order?',
    ]);

    Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
        return $mail->contactMessage->email === 'taylor@example.test'
            && $mail->hasTo(config('mail.from.address'));
    });
});

test('contact notification renders a branded and escaped html email', function () {
    $contactMessage = ContactMessage::create([
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'subject' => 'Order question',
        'message' => '<script>alert("not markup")</script>',
    ]);

    $html = (new ContactMessageReceived($contactMessage))->render();

    expect($html)
        ->toContain('A customer would love to hear from you')
        ->toContain('&lt;script&gt;alert(&quot;not markup&quot;)&lt;/script&gt;')
        ->not->toContain('<script>alert');
});

test('admins can view contact messages and email a reply', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::create([
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'subject' => 'Order question',
        'message' => 'Could you help me with my order?',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts'))
        ->assertOk()
        ->assertSee('Contact inbox')
        ->assertSee('Taylor Customer')
        ->assertSee(route('admin.contacts.show', $message), false)
        ->assertSee('Could you help me with my order?');

    expect($message->fresh()->read_at)->toBeNull();

    $this->get(route('admin.contacts.show', $message))
        ->assertOk()
        ->assertSee('Customer message')
        ->assertSee('Could you help me with my order?')
        ->assertSee('Send reply by email');
    expect($message->fresh()->read_at)->not->toBeNull();

    $this->post(route('admin.contacts.reply', $message), [
        'reply' => 'Of course, please share your order number.',
    ])
        ->assertRedirect()
        ->assertSessionHas('contact_reply_success');

    $this->assertDatabaseHas('contact_messages', [
        'id' => $message->id,
        'admin_reply' => 'Of course, please share your order number.',
    ]);
    expect($message->fresh()->replied_at)->not->toBeNull();

    Mail::assertSent(ContactMessageReply::class, function (ContactMessageReply $mail) use ($message) {
        $html = $mail->render();

        return $mail->hasTo($message->email)
            && $mail->customerName === $message->name
            && $mail->originalMessage === $message->message
            && str_contains($html, 'We’re glad you got in touch')
            && str_contains($html, 'Could you help me with my order?');
    });
});

test('customers cannot access the admin contact inbox', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get(route('admin.contacts'))
        ->assertRedirect(route('user.dashboard'));
});

test('contact reply is saved even when mail delivery fails', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::create([
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'subject' => 'Order question',
        'message' => 'Could you help me with my order?',
    ]);
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP unavailable'));

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $message))
        ->assertOk();

    $this->from(route('admin.contacts.show', $message))
        ->post(route('admin.contacts.reply', $message), [
            'reply' => 'I have saved this reply while mail delivery is unavailable.',
        ])
        ->assertRedirect()
        ->assertSessionHas('contact_reply_error');

    $this->assertDatabaseHas('contact_messages', [
        'id' => $message->id,
        'admin_reply' => 'I have saved this reply while mail delivery is unavailable.',
    ]);

    $this->get(route('admin.contacts.show', $message))
        ->assertOk()
        ->assertSee('Your reply was saved, but email delivery failed.')
        ->assertSee('I have saved this reply while mail delivery is unavailable.');
});

test('admins can delete a contact conversation with success feedback', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::create([
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'subject' => 'Order question',
        'message' => 'Could you help me with my order?',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.contacts.destroy', $message))
        ->assertRedirect(route('admin.contacts'))
        ->assertSessionHas('contact_delete_success', 'Contact message deleted.');

    $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    $this->get(route('admin.contacts'))
        ->assertOk()
        ->assertSee('Contact message deleted.');
});
