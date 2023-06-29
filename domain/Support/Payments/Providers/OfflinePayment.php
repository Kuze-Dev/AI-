<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Providers;

use Aws\Arn\Exception\InvalidArnException;
use Domain\Support\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Support\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Support\Payments\DataTransferObjects\PaymentGateway\PaymentRefund;
use Domain\Support\Payments\Events\PaymentProcessEvent;
use Domain\Support\Payments\Models\Payment;

class OfflinePayment extends Provider
{
    protected string $name = 'offline';

    public function authorize(): PaymentAuthorize
    {

        $providerData = $this->data;

        $paymentData = $providerData->transactionData->amount;

        $providerData->model->payments()->create([
            'payment_method_id' => $providerData->payment_method_id,
            'gateway' => $this->name,
            'amount' => $paymentData->total,
            'status' => 'pending',
        ]);

        return new PaymentAuthorize(true);
    }

    public function refund(): PaymentRefund
    {
        return new PaymentRefund(success: false);
    }

    public function capture(Payment $paymentModel, array $data): PaymentCapture
    {
        return match ($data['status']) {
            'success' => $this->processTransaction($paymentModel, $data),
            default => throw new InvalidArnException(),
        };
    }

    protected function processTransaction(Payment $paymentModel, array $data): PaymentCapture
    {

        $paymentModel->update([
            'status' => 'paid',
        ]);

        event(new PaymentProcessEvent($paymentModel));

        return new PaymentCapture(success: true);

    }
}
