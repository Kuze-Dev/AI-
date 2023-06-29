<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Notifications\PasswordHasBeenReset;
use Domain\Customer\Notifications\ResetPassword;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

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
        ->createOne();

    Queue::fake();
    Notification::fake();

    $token = PasswordBroker::broker('customer')->createToken($customer);

    postJson('api/password/reset', [
        'token' => ray()->pass($token),
        'email' => $customer->email,
        'password' => '1234',
    ])
        ->assertValid()
        ->assertOk()
        ->assertJson(['message' => 'Your password has been reset!']);

    Notification::assertSentTo([$customer], PasswordHasBeenReset::class);
});
