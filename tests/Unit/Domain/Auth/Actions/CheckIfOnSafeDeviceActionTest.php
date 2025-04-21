<?php

declare(strict_types=1);

use Domain\Auth\Actions\CheckIfOnSafeDeviceAction;
use Domain\Auth\Model\SafeDevice;
use Illuminate\Http\Request;
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
    $this->user->twoFactorAuthentication->safeDevices()
        ->firstOrNew()
        ->forceFill([
            'ip' => '0.0.0.0',
            'user_agent' => 'user-agent',
            'remember_token' => 'secret',
        ])
        ->save();
    $this->safeDevice = SafeDevice::first();
})->skip('skip otp');

it('can check if on safe device', function () {
    $this->mock(
        Request::class,
        fn (MockInterface $mock) => $mock->expects('cookie')->andReturns('secret')
    );

    $result = app(CheckIfOnSafeDeviceAction::class)->execute($this->user);

    expect($result)->tobeTrue();
});

it('can check if not on safe device', function () {
    $this->mock(
        Request::class,
        fn (MockInterface $mock) => $mock->expects('cookie')->andReturns(null)
    );

    $result = app(CheckIfOnSafeDeviceAction::class)->execute($this->user);

    expect($result)->tobefalse();
});

it('returns false when two factor is disabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => null]);
    $this->user->refresh();
    $this->mock(
        Request::class,
        fn (MockInterface $mock) => $mock->shouldNotReceive('cookie')
    );

    $result = app(CheckIfOnSafeDeviceAction::class)->execute($this->user);

    expect($result)->tobefalse();
});
