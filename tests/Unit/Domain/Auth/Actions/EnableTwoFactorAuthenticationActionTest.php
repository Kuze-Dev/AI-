<?php

declare(strict_types=1);

use Domain\Auth\Actions\EnableTwoFactorAuthenticationAction;
use Domain\Auth\Actions\ValidateTotpCodeAction;
use Domain\Auth\Events\TwoFactorAuthenticationEnabled;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    Event::fake();
    $this->user = User::create(['email' => 'test@user']);
    $this->user->twoFactorAuthentication()
        ->firstOrNew()
        ->forceFill(['secret' => 'secret'])
        ->save();
});

it('can enable two factor authentication', function () {
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->shouldReceive('execute')->andReturn(true)
    );
    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($this->user, 'valid code');

    expect($result)->toBeTrue();
    assertDatabaseCount('two_factor_authentications', 1);
    Event::assertDispatched(TwoFactorAuthenticationEnabled::class);
});

it('won\'t enable two factor authentication on invalid code ', function () {
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->shouldReceive('execute')->andReturn(false)
    );
    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($this->user, 'valid code');

    expect($result)->toBeFalse();
    assertDatabaseCount('two_factor_authentications', 1);
    Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class);
});

it('will reload two factor authentication if loaded in relations', function () {
    $this->user->load('twoFactorAuthentication');

    $user = mock($this->user)->expect(load: fn () => $this->user);
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->shouldReceive('execute')->andReturn(true)
    );

    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($user, 'secret');

    expect($result)->toBeTrue();
    assertDatabaseCount('two_factor_authentications', 1);
    Event::assertDispatched(TwoFactorAuthenticationEnabled::class);
});

it('does nothing if already enabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => now()]);

    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($this->user, 'valid code');

    expect($result)->toBeNull();
    Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class);
});
