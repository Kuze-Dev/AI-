<?php

use Domain\Auth\Actions\ValidateTotpCodeAction;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Fixtures\User;

beforeEach(function () {
    Event::fake();
    $this->user = User::create(['email' => 'test@user']);
    $this->user->twoFactorAuthentication()
        ->firstOrNew()
        ->forceFill([
            'enabled_at' => now(),
            'secret' => 'secret',
        ])
        ->save();
});

it('can validate topt code', function () {
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->shouldReceive('verify')->andReturn(true)
    );

    $result = app(ValidateTotpCodeAction::class)->execute($this->user, 'secret');

    expect($result)->toBeTrue();
});

it('won\'t validate invalid topt code', function () {
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->shouldReceive('verify')->andReturn(false)
    );

    $result = app(ValidateTotpCodeAction::class)->execute($this->user, 'secret');

    expect($result)->toBeFalse();
});
