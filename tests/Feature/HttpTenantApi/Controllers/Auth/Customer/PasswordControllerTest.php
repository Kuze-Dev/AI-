<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use Domain\Auth\Events\PasswordResetSent;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Notifications\ResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertTrue;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext([
        CustomerBase::class,
        AddressBase::class,
        TierBase::class,
    ]);
});

it('can send link', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Event::fake(PasswordResetSent::class);
    Notification::fake();
    Queue::fake();

    postJson('api/account/password/email', ['email' => $customer->email])
        ->assertValid()
        ->assertOk()
        ->assertJson(['message' => 'We have emailed your password reset link.']);

    Notification::assertSentTo([$customer], ResetPassword::class);
    Event::assertDispatched(PasswordResetSent::class);
});

it('can reset password', function () {
    $customer = CustomerFactory::new()
        ->createOne([
            'password' => 'old-password',
            'remember_token' => 'old-remember_token',
        ]);

    Event::fake(PasswordReset::class);
    Notification::fake();
    Queue::fake();

    postJson('api/account/password/reset', [
        'token' => PasswordBroker::broker('customer')->createToken($customer),
        'email' => $customer->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertValid()
        ->assertOk()
        ->assertJson(['message' => 'Your password has been reset.']);

    $customer->refresh();

    assertTrue(Hash::check('new-password', $customer->password), 'password not reset');
    assertNotSame('old-remember_token', $customer->getRememberToken());

    Event::assertDispatched(PasswordReset::class);
});

it('can not update password', function () {
    $customer = CustomerFactory::new()
        ->createOne([
            'password' => 'old-password',
        ]);

    Sanctum::actingAs($customer);

    putJson('api/account/password', [
        'current_password' => 'invalid-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertInvalid(['current_password' => 'Invalid current password.'])
        ->assertUnprocessable();

    $customer->refresh();

    assertTrue(Hash::check('old-password', $customer->password), 'password updated');
});

it('can update password', function () {
    $customer = CustomerFactory::new()
        ->createOne([
            'password' => 'old-password',
        ]);

    Sanctum::actingAs($customer);

    putJson('api/account/password', [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertValid()
        ->assertOk()
        ->assertJson(['message' => 'Your password has been updated!']);

    $customer->refresh();

    assertTrue(Hash::check('new-password', $customer->password), 'password not updated');
});
