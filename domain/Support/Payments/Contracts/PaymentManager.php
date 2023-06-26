<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\Providers\Provider;

interface PaymentManager
{
    public function provider($provider): Provider;

    public function extend($provider, $callback): void;
}
