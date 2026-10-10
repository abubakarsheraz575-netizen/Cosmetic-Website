<?php

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Mail\AdminActivityNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('user and admin support chat pages render their AJAX interfaces', function () {
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->get('/user/support')
        ->assertOk()
        ->assertSee('supportChatRoot');

    $this->actingAs($admin)
        ->get('/admin/support')
        ->assertOk()
        ->assertSee('supportApp')
        ->assertSee('id="menuBtn"', false)
        ->assertSee('id="chatForm"', false)
        ->assertSee('.support-conversation{min-width:0;min-height:0', false)
        ->assertSee('grid-template-rows:minmax(0,1fr)', false)
        ->assertSee('.chat-compose{display:block;flex:0 0 auto', false);
});

test('admin login can reach the support inbox after the support schema is installed', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect('/admin/dashboard');

    $this->get('/admin/support')
        ->assertOk()
        ->assertSee('supportApp');
});

test('admin inbox lists every customer and can start a conversation with a new customer', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customers = User::factory()->count(3)->create(['role' => 'user']);

    $this->actingAs($admin)
        ->getJson('/admin/support/inbox')
        ->assertOk()
        ->assertJsonPath('pagination.total', 3)
        ->assertJsonCount(3, 'conversations')
        ->assertJsonPath('conversations.0.last_message', null);

    $newCustomer = $customers->last();
    $this->postJson("/admin/support/users/{$newCustomer->id}/conversation")
        ->assertOk()
        ->assertJsonStructure(['id']);

    expect(SupportConversation::where('user_id', $newCustomer->id)->exists())->toBeTrue();

    $this->getJson('/admin/support/inbox')
        ->assertOk()
        ->assertJsonPath('pagination.total', 3)
        ->assertJsonCount(3, 'conversations');
});

test('support inbox searches and paginates the complete customer list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(31)->create(['role' => 'user']);
    $match = User::factory()->create([
        'role' => 'user',
        'name' => 'Findable Customer',
        'email' => 'findable@example.test',
    ]);

    $this->actingAs($admin)
        ->getJson('/admin/support/inbox')
        ->assertOk()
        ->assertJsonPath('pagination.total', 32)
        ->assertJsonPath('pagination.per_page', 30)
        ->assertJsonCount(30, 'conversations');

    $this->getJson('/admin/support/inbox?page=2')
        ->assertOk()
        ->assertJsonPath('pagination.current_page', 2)
        ->assertJsonCount(2, 'conversations');

    $this->getJson('/admin/support/inbox?search=findable%40example.test')
        ->assertOk()
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('conversations.0.user.id', $match->id);
});

test('a user and admin can exchange messages and read state updates', function () {
    Mail::fake();
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->postJson('/user/support/messages', ['body' => 'I need help with my order.'])
        ->assertCreated()
        ->assertJsonPath('message.body', 'I need help with my order.')
        ->assertJsonPath('admin_notified', true);
    Mail::assertSent(AdminActivityNotification::class, function (AdminActivityNotification $mail) use ($admin, $user) {
        return $mail->activityTitle === 'New customer support message'
            && $mail->hasTo($admin->email)
            && str_contains($mail->details['Customer'], $user->email);
    });

    $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();
    $userMessage = $conversation->messages()->firstOrFail();

    $this->actingAs($user)
        ->postJson('/user/support/heartbeat', ['typing' => true])
        ->assertOk();

    $this->actingAs($admin)
        ->getJson("/admin/support/{$conversation->id}/messages")
        ->assertOk()
        ->assertJsonPath('messages.0.body', 'I need help with my order.')
        ->assertJsonPath('presence.user_online', true)
        ->assertJsonPath('presence.user_typing', true);

    expect($userMessage->fresh()->read_at)->not->toBeNull();

    $this->actingAs($admin)
        ->postJson("/admin/support/{$conversation->id}/messages", ['body' => 'We are looking into it.'])
        ->assertCreated()
        ->assertJsonPath('message.body', 'We are looking into it.');

    $this->actingAs($admin)
        ->postJson("/admin/support/{$conversation->id}/heartbeat", ['typing' => true])
        ->assertOk();

    $this->actingAs($user)
        ->getJson('/user/support/messages')
        ->assertOk()
        ->assertJsonPath('messages.1.body', 'We are looking into it.')
        ->assertJsonPath('presence.admin_typing', true);
});

test('support messages are isolated to their customer conversation', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);

    $this->actingAs($owner)
        ->postJson('/user/support/messages', ['body' => 'Private account details'])
        ->assertCreated();

    $conversation = SupportConversation::where('user_id', $owner->id)->firstOrFail();
    $message = $conversation->messages()->firstOrFail();

    $this->actingAs($otherUser)
        ->getJson('/user/support/messages')
        ->assertOk()
        ->assertJsonMissing(['body' => 'Private account details']);

    $this->get(route('support.attachments.download', $message))
        ->assertNotFound();
});

test('support attachments are stored privately and only the conversation owner or admin can download them', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->postJson('/user/support/messages', [
            'attachment' => UploadedFile::fake()->create('routine.txt', 2, 'text/plain'),
        ])
        ->assertCreated();

    $message = SupportMessage::firstOrFail();
    Storage::disk('local')->assertExists($message->attachment_path);

    $this->actingAs($otherUser)
        ->get(route('support.attachments.download', $message))
        ->assertNotFound();

    $this->actingAs($user)
        ->get(route('support.attachments.download', $message))
        ->assertDownload('routine.txt');

    $this->actingAs($admin)
        ->get(route('support.attachments.download', $message))
        ->assertDownload('routine.txt');
});

test('image attachments have a private inline preview and remain downloadable', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=');

    $response = $this->actingAs($user)
        ->postJson('/user/support/messages', [
            'attachment' => UploadedFile::fake()->createWithContent('routine.png', $png),
        ])
        ->assertCreated()
        ->assertJsonPath('message.attachment.is_image', true)
        ->assertJsonPath('message.attachment.name', 'routine.png');

    $message = SupportMessage::firstOrFail();
    $previewUrl = $response->json('message.attachment.preview_url');
    expect($previewUrl)->toBeString()->not->toBeEmpty();

    $this->actingAs($otherUser)
        ->get($previewUrl)
        ->assertNotFound();

    $this->actingAs($user)
        ->get($previewUrl)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->actingAs($admin)
        ->get($previewUrl)
        ->assertOk();

    $this->actingAs($user)
        ->get(route('support.attachments.download', $message))
        ->assertDownload('routine.png');
});

test('support messages can be quoted and include an image thumbnail for the reply preview', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQe0AAAAASUVORK5CYII=');

    $original = $this->actingAs($user)
        ->postJson('/user/support/messages', [
            'body' => 'Here is the photo.',
            'attachment' => UploadedFile::fake()->createWithContent('routine.png', $png),
        ])
        ->assertCreated()
        ->json('message');

    $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();
    $reply = $this->actingAs($admin)
        ->postJson("/admin/support/{$conversation->id}/messages", [
            'body' => 'I can see the image.',
            'reply_to_message_id' => $original['id'],
        ])
        ->assertCreated()
        ->assertJsonPath('message.reply.id', $original['id'])
        ->assertJsonPath('message.reply.body', 'Here is the photo.')
        ->assertJsonPath('message.reply.attachment.is_image', true)
        ->assertJsonPath('message.reply.attachment.preview_url', $original['attachment']['preview_url']);

    $this->actingAs($user)
        ->getJson('/user/support/messages')
        ->assertOk()
        ->assertJsonPath('messages.1.reply.sender_name', $user->name)
        ->assertJsonPath('messages.1.reply.attachment.name', 'routine.png');

    expect($reply->json('message.reply.attachment.preview_url'))->toBe($original['attachment']['preview_url']);
});

test('users and admins can edit their own messages and pin star or report messages', function () {
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);

    $userMessage = $this->actingAs($user)
        ->postJson('/user/support/messages', ['body' => 'Original question'])
        ->assertCreated()
        ->json('message');
    $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)
        ->patchJson("/user/support/messages/{$userMessage['id']}", ['body' => 'Updated question'])
        ->assertOk()
        ->assertJsonPath('message.body', 'Updated question')
        ->assertJsonPath('message.edited_at', fn ($value) => is_string($value));

    $this->actingAs($admin)
        ->patchJson("/admin/support/{$conversation->id}/messages/{$userMessage['id']}", ['body' => 'Admin cannot impersonate the customer'])
        ->assertForbidden();

    $adminMessage = $this->actingAs($admin)
        ->postJson("/admin/support/{$conversation->id}/messages", ['body' => 'We can help.'])
        ->assertCreated()
        ->json('message');

    $this->actingAs($admin)
        ->patchJson("/admin/support/{$conversation->id}/messages/{$adminMessage['id']}", ['body' => 'We are happy to help.'])
        ->assertOk()
        ->assertJsonPath('message.body', 'We are happy to help.');

    $this->actingAs($user)
        ->postJson("/user/support/messages/{$adminMessage['id']}/pin")
        ->assertOk()
        ->assertJsonPath('is_pinned', true);
    $this->postJson("/user/support/messages/{$adminMessage['id']}/star")
        ->assertOk()
        ->assertJsonPath('is_starred', true);
    $this->postJson("/user/support/messages/{$adminMessage['id']}/report", ['reason' => 'The reply was inappropriate.'])
        ->assertOk()
        ->assertJsonPath('reported', true);

    $this->actingAs($admin)
        ->getJson("/admin/support/{$conversation->id}/messages")
        ->assertOk()
        ->assertJsonPath('messages.1.is_pinned', true)
        ->assertJsonPath('messages.1.report_count', 1)
        ->assertJsonPath('messages.1.is_starred', false);

    $this->actingAs($user)
        ->getJson('/user/support/messages')
        ->assertOk()
        ->assertJsonPath('messages.1.is_starred', true);
});

test('message deletion is synchronized to the other side and attachment files are removed', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)
        ->postJson('/user/support/messages', [
            'body' => 'Please remove this file.',
            'attachment' => UploadedFile::fake()->create('private.txt', 2, 'text/plain'),
        ])
        ->assertCreated();
    $message = SupportMessage::firstOrFail();
    $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();
    $cursor = now()->subMinute()->toIso8601String();

    $this->actingAs($user)
        ->deleteJson("/user/support/messages/{$message->id}")
        ->assertOk()
        ->assertJsonPath('deleted', true);

    $this->actingAs($admin)
        ->getJson("/admin/support/{$conversation->id}/messages?after={$message->id}&changed_after=".urlencode($cursor))
        ->assertOk()
        ->assertJsonPath('messages.0.id', $message->id)
        ->assertJsonPath('messages.0.deleted', true)
        ->assertJsonPath('messages.0.body', null)
        ->assertJsonPath('messages.0.attachment', null);

    $this->assertSoftDeleted('support_messages', ['id' => $message->id]);
    Storage::disk('local')->assertMissing($message->attachment_path);

    $this->actingAs($admin)
        ->deleteJson("/admin/support/{$conversation->id}/messages/{$message->id}")
        ->assertNotFound();
});

test('deleting a conversation permanently removes it and all its attachments for both sides', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->postJson('/user/support/messages', [
            'attachment' => UploadedFile::fake()->create('conversation.txt', 2, 'text/plain'),
        ])
        ->assertCreated();
    $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();
    $message = $conversation->messages()->firstOrFail();

    $this->actingAs($user)
        ->deleteJson("/admin/support/{$conversation->id}")
        ->assertRedirect(route('user.dashboard', absolute: false));

    $this->actingAs($admin)
        ->deleteJson("/admin/support/{$conversation->id}")
        ->assertOk()
        ->assertJsonPath('deleted', true);

    $this->assertDatabaseMissing('support_conversations', ['id' => $conversation->id]);
    $this->assertDatabaseMissing('support_messages', ['id' => $message->id]);
    Storage::disk('local')->assertMissing($message->attachment_path);
});

test('support attachments reject unsupported file types', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->postJson('/user/support/messages', [
            'attachment' => UploadedFile::fake()->create('payload.svg', 2, 'image/svg+xml'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('attachment');
});
