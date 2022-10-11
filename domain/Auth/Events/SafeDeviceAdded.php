<?php

namespace Domain\Auth\Events;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Illuminate\Http\Request;

class SafeDeviceAdded
{
    public function __construct(
        public TwoFactorAuthenticatable $user,
        public Request $request
    ) {
    }
}
