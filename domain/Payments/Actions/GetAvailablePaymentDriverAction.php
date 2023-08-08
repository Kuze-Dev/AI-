<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use App\Features\ECommerce\BankTransfer;
use App\Features\ECommerce\OfflineGateway;
use App\Features\ECommerce\PaypalGateway;
use App\Features\ECommerce\StripeGateway;

class GetAvailablePaymentDriverAction
{
    public function execute(): array
    {

        if (tenancy()->initialized) {

            $tenant = tenancy()->tenant;

            return array_filter([
                'paypal' => $tenant?->features()->active(app(PaypalGateway::class)->name) ? app(PaypalGateway::class)->label : false,
                'stripe' => $tenant?->features()->active(app(StripeGateway::class)->name) ? app(StripeGateway::class)->label : false,
                'manual' => $tenant?->features()->active(app(OfflineGateway::class)->name) ? app(OfflineGateway::class)->label : false,
                'bank-transfer' => $tenant?->features()->active(app(BankTransfer::class)->name) ? app(BankTransfer::class)->label : false,
            ], fn ($value) => $value !== false);
        }

        return [];

    }
}
