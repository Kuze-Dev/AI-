<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\PaymentMethod\Models\PaymentMethod;

readonly class PaymentMethodOrderData
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $gateway,
        public ?string $subtitle,
        public ?string $description,
        public ?string $instruction,
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
