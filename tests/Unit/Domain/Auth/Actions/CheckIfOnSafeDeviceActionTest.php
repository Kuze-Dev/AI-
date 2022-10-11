<?php

use Domain\Auth\Actions\CheckIfOnSafeDeviceAction;
use Domain\Auth\Model\SafeDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
});

it('can check if on safe device', function () {
    $request = mock(Request::class)->expect(cookie: fn (string $key) => 'secret');

    $result = app(CheckIfOnSafeDeviceAction::class)->execute($this->user, $request);

    expect($result)->tobeTrue();
});

it('can check if not on safe device', function () {
    $request = mock(Request::class)->expect(cookie: fn (string $key) => null);

    $result = app(CheckIfOnSafeDeviceAction::class)->execute($this->user, $request);

    expect($result)->tobefalse();
});

it('returns false when two factor is disabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => null]);
    $this->user->refresh();
    $request = mock(Request::class)
        ->shouldNotReceive('cookie')
        ->getMock();

    $result = app(CheckIfOnSafeDeviceAction::class)->execute($this->user, $request);

    expect($result)->tobefalse();
});
