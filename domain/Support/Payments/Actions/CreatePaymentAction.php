<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Payments\Contracts\PaymentManagerInterface;
use Domain\Support\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Support\Payments\DataTransferObjects\ProviderData;
use Throwable;

class CreatePaymentAction
{
    /** Execute create collection query. */
    public function execute(PaymentMethod $paymentMethod, ProviderData $ProviderData): PaymentAuthorize
    {
        try {
            return app(PaymentManagerInterface::class)
                ->driver($paymentMethod->slug)
                ->withData($ProviderData)
                ->authorize();

        } catch (Throwable $th) {
            throw $th;
        }

    }
}
