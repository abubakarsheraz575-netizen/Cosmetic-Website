<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200)
        ->assertSee('Check your inbox')
        ->assertSee($user->email)
        ->assertSee('Resend verification email');
});

test('verification email uses the branded html and plain text templates', function () {
    $user = User::factory()->unverified()->create();
    $mail = (new VerifyEmailNotification)->toMail($user);

    expect($mail->view['html'])->toBe('emails.verify-email')
        ->and($mail->view['text'])->toBe('emails.text.verify-email')
        ->and($mail->viewData['user']->is($user))->toBeTrue()
        ->and(view($mail->view['html'], $mail->viewData)->render())->toContain('Your glow starts here')
        ->and(view($mail->view['text'], $mail->viewData)->render())->toContain('Confirm your email address');
});

test('password reset email uses the branded html and plain text templates', function () {
    $user = User::factory()->create();
    $mail = (new ResetPasswordNotification('test-token'))->toMail($user);

    expect($mail->view['html'])->toBe('emails.reset-password')
        ->and($mail->view['text'])->toBe('emails.text.reset-password')
        ->and($mail->viewData['user']->is($user))->toBeTrue()
        ->and(view($mail->view['html'], $mail->viewData)->render())->toContain('Let’s get you back in')
        ->and(view($mail->view['text'], $mail->viewData)->render())->toContain('We received a request to reset your password');
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('user.dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->get($verificationUrl)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification link works when another account is signed in', function () {
    $user = User::factory()->unverified()->create();
    $otherUser = User::factory()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($otherUser)->get($verificationUrl)
        ->assertRedirect(route('user.dashboard', absolute: false).'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('registered account can verify through its emailed link after signing out', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'New Customer',
        'email' => 'new-customer@example.test',
        'password' => 'ValidPassword123!',
        'password_confirmation' => 'ValidPassword123!',
    ])->assertRedirect(route('verification.notice', absolute: false));

    $user = User::where('email', 'new-customer@example.test')->firstOrFail();
    $verificationUrl = null;

    Notification::assertSentTo($user, VerifyEmailNotification::class, function ($notification) use ($user, &$verificationUrl) {
        $mail = $notification->toMail($user);
        $verificationUrl = $mail->viewData['url'];

        expect($mail->view['html'])->toBe('emails.verify-email')
            ->and($mail->view['text'])->toBe('emails.text.verify-email');

        return true;
    });

    $this->post('/logout')->assertRedirect('/');
    $this->get($verificationUrl)
        ->assertRedirect(route('user.dashboard', absolute: false).'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
