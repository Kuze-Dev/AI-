<?php

declare(strict_types=1);

namespace Domain\Payments;

use Illuminate\Support\Manager;

// use App\Services\Payments\Contracts\PaymentManager as PaymentManagerContract;
// use App\Services\Payments\Providers\Provider;

class PaymentManager extends Manager
{
    /**
     * Get the default driver name.
     */
    #[\Override]
    public function getDefaultDriver(): string
    {
        return config('payment-gateway.default');
    }
}
