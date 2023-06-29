<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Notifications\PasswordHasBeenReset;
use Domain\Customer\Notifications\ResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    testInTenantContext();
});

it('can send link', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Queue::fake();
    Notification::fake();

    postJson('api/password/email', ['email' => $customer->email])
        ->assertValid()
        ->assertOk()
        ->assertJson(['message' => 'We have emailed your password reset link!']);

    Notification::assertSentTo([$customer], ResetPassword::class);
});

it('can reset password', function () {
    $customer = CustomerFactory::new()
        ->createOne(['password' => 'old-password']);

    Event::fake();
    Notification::fake();
    Queue::fake();

    $token = PasswordBroker::broker('customer')->createToken($customer);

    postJson('api/password/reset', [
        'token' => ray()->pass($token),
        'email' => $customer->email,
        'password' => 'new-password',
    ])
        ->assertValid()
        ->assertOk()
        ->assertJson(['message' => 'Your password has been reset!']);

    $customer->refresh();

    assertTrue(Hash::check('new-password', $customer->password), 'password not reset');

    Notification::assertSentTo([$customer], PasswordHasBeenReset::class);
    Event::assertDispatched(PasswordReset::class);
});
