<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use App\Features\Shopconfiguration\PaymentGateway\BankTransfer;
use App\Features\Shopconfiguration\PaymentGateway\OfflineGateway;
use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\Features\Shopconfiguration\PaymentGateway\StripeGateway;
use App\Features\Shopconfiguration\PaymentGateway\VisionpayGateway;

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
                'vision-pay' => $tenant?->features()->active(app(VisionpayGateway::class)->name) ? app(VisionpayGateway::class)->label : false,
            ], fn ($value) => $value !== false);
        }

        return [];

    }
}
