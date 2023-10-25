<?php

declare(strict_types=1);

namespace Domain\Payments\Interfaces;

use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentRefund;
use Domain\Payments\Models\Payment;

interface PaymentInterface
{
    public function setConfig(array $config): self;

    /**
     * Authorize the payment.
     */
    public function authorize(): PaymentAuthorize;

    public function refund(Payment $paymentModel, int $amount): PaymentRefund;

    public function capture(Payment $paymentModel, array $data): PaymentCapture;
}
