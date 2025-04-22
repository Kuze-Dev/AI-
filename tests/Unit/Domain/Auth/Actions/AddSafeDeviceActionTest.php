<?php

declare(strict_types=1);

use Domain\Auth\Actions\AddSafeDeviceAction;
use Domain\Auth\DataTransferObjects\SafeDeviceData;
use Domain\Auth\Events\SafeDeviceAdded;
use Domain\Auth\Model\SafeDevice;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

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
})->skip('skip otp');

it('can add safe device', function () {
    app(AddSafeDeviceAction::class)->execute($this->user, new SafeDeviceData('0.0.0.0', 'user-agent'));

    assertDatabaseHas(SafeDevice::class, [
        'ip' => '0.0.0.0',
        'user_agent' => 'user-agent',
    ]);
    Event::assertDispatched(SafeDeviceAdded::class);
});

it('can ensure max safe devices is not overflown', function () {
    foreach (range(0, config('domain.auth.two_factor.safe_devices.max_devices') + 1) as $i) {
        app(AddSafeDeviceAction::class)->execute($this->user, new SafeDeviceData('0.0.0.0', 'user-agent'));
    }

    assertDatabaseCount(SafeDevice::class, config('domain.auth.two_factor.safe_devices.max_devices'));
});

it('does nothing when two factor is disabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => null]);

    app(AddSafeDeviceAction::class)->execute($this->user, new SafeDeviceData('0.0.0.0', 'user-agent'));

    Event::assertNotDispatched(SafeDeviceAdded::class);
});
