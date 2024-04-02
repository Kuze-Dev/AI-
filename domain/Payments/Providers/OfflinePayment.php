<?php

declare(strict_types=1);

namespace Domain\Payments\Providers;

use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentRefund;
use Domain\Payments\Enums\PaymentStatus;
use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Models\Payment;
use InvalidArgumentException;

class OfflinePayment extends Provider
{
    protected string $name = 'offline';

    #[\Override]
    public function authorize(): PaymentAuthorize
    {

        return new PaymentAuthorize(true);
    }

    #[\Override]
    public function refund(Payment $paymentModel, int $amount): PaymentRefund
    {
        return new PaymentRefund(success: false);
    }

    #[\Override]
    public function capture(Payment $paymentModel, array $data): PaymentCapture
    {
        return match ($data['status']) {
            'success' => $this->processTransaction($paymentModel, $data),
            default => throw new InvalidArgumentException(),
        };
    }

    protected function processTransaction(Payment $paymentModel, array $data): PaymentCapture
    {

        $paymentModel->update([
            'status' => PaymentStatus::PAID->value,
        ]);

        event(new PaymentProcessEvent($paymentModel));

        return new PaymentCapture(success: true);

    }
}
