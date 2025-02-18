<?php

declare(strict_types=1);

use Domain\Auth\Actions\FlushSafeDevicesAction;
use Domain\Auth\Events\SafeDevicesFlushed;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseCount;
beforeEach()->skip('skip otp');

it('can flush safe devices', function () {
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

    app(FlushSafeDevicesAction::class)->execute($this->user);

    assertDatabaseCount('safe_devices', 0);
    Event::assertDispatched(SafeDevicesFlushed::class);
});
