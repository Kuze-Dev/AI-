<?php

declare(strict_types=1);

use Domain\Auth\Actions\AddSafeDeviceAction;
use Domain\Auth\Events\SafeDeviceAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseCount;

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

it('can add safe device', function () {
    $request = mock(Request::class)
        ->expect(
            ip: fn () => '0.0.0.0',
            userAgent: fn () => 'user-agent',
        );

    app(AddSafeDeviceAction::class)->execute($this->user, $request);

    assertDatabaseCount('safe_devices', 1);
    Event::assertDispatched(SafeDeviceAdded::class);
});

it('can ensure max safe devices is not overflown', function () {
    $request = mock(Request::class)
        ->expect(
            ip: fn () => '0.0.0.0',
            userAgent: fn () => 'user-agent',
        );

    foreach (range(0, config('domain.auth.two_factor.safe_devices.max_devices') + 1) as $i) {
        app(AddSafeDeviceAction::class)->execute($this->user, $request);
    }

    assertDatabaseCount('safe_devices', config('domain.auth.two_factor.safe_devices.max_devices'));
});

it('does nothing when two factor is disabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => null]);

    app(AddSafeDeviceAction::class)->execute($this->user, app(Request::class));

    Event::assertNotDispatched(SafeDeviceAdded::class);
});
