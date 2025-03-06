<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use App\Features\Shopconfiguration\PaymentGateway\BankTransfer;
use App\Features\Shopconfiguration\PaymentGateway\OfflineGateway;
use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\Features\Shopconfiguration\PaymentGateway\StripeGateway;
use App\Features\Shopconfiguration\PaymentGateway\VisionpayGateway;
use Domain\Tenant\TenantFeatureSupport;

class GetAvailablePaymentDriverAction
{
    public function execute(): array
    {
        return array_filter([
            'paypal' => TenantFeatureSupport::active(PaypalGateway::class)
                ? app(PaypalGateway::class)->getLabel()
                : false,
            'stripe' => TenantFeatureSupport::active(StripeGateway::class)
                ? app(StripeGateway::class)->getLabel()
                : false,
            'manual' => TenantFeatureSupport::active(OfflineGateway::class)
                ? app(OfflineGateway::class)->getLabel()
                : false,
            'bank-transfer' => TenantFeatureSupport::active(BankTransfer::class)
                ? app(BankTransfer::class)->getLabel()
                : false,
            'vision-pay' => TenantFeatureSupport::active(VisionpayGateway::class)
                ? app(VisionpayGateway::class)->getLabel()
                : false,
        ], fn ($value) => $value !== false);

    }
}
