<?php

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Events\SafeDevicesFlushed;
use Illuminate\Support\Facades\Event;

class FlushSafeDevicesAction
{
    public function execute(TwoFactorAuthenticatable $authenticatable): TwoFactorAuthenticatable
    {
        $authenticatable->twoFactorAuthentication->safeDevices()
            ->delete();

        Event::dispatch(new SafeDevicesFlushed($authenticatable));

        return $authenticatable;
    }
}
