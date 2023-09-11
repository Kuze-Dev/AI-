<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\PaymentMethod\Models\PaymentMethod;

class PaymentMethodOrderData
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $gateway,
        public readonly ?string $subtitle,
        public readonly ?string $description,
        public readonly ?string $instruction,
    ) {
    }

    public static function fromPaymentMethod(PaymentMethod $paymentMethod): self
    {
        return new self(
            title: $paymentMethod->title,
            slug: $paymentMethod->slug,
            gateway: $paymentMethod->gateway,
            subtitle: $paymentMethod->subtitle ?? null,
            description: $paymentMethod->description ?? null,
            instruction: $paymentMethod->instruction ?? null,
        );
    }
}
