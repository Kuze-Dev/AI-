<?php

declare(strict_types=1);

namespace Domain\Payments\Providers;

use App\Settings\PaymentSettings;
use Domain\Payments\API\VisionPay\Client as VisionPayClient;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentRefund;
use Domain\Payments\Enums\PaymentStatus;
use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Models\Payment;
use InvalidArgumentException;

class VisionPayProvider extends Provider
{
    protected string $name = 'vision-pay';

    protected VisionPayClient $visionPayClient;

    public function __construct()
    {
        /** @var \App\Settings\PaymentSettings $paymentSettings */
        $paymentSettings = app(PaymentSettings::class);

        if ($paymentSettings->vision_pay_apiKey) {
            $this->visionPayClient = new VisionPayClient(
                $paymentSettings->vision_pay_apiKey,
                $paymentSettings->vision_pay_production_mode
            );
        }
    }

    public function authorize(): PaymentAuthorize
    {

        try {

            $providerData = $this->data;

            $this->visionPayClient->authenticate();

            $data = [
                'token' => $this->visionPayClient->getJwtToken(),
                'success_url' => route(
                    'tenant.api.payment-callback',
                    [
                        'paymentmethod' => $providerData->payment_method_id,
                        'transactionId' => $providerData->paymentModel->id,
                        'status' => 'success',  ]
                ),
                'cancel_url' => route(
                    'tenant.api.payment-callback',
                    [
                        'paymentmethod' => $providerData->payment_method_id,
                        'transactionId' => $providerData->paymentModel->id,
                        'status' => 'cancelled',  ]
                ),
            ];

            return PaymentAuthorize::fromArray([
                'success' => true,
                'message' => 'Authenticated',
                'data' => $data,
            ]);

        } catch (\Throwable) {

            return new PaymentAuthorize(false);
        }

    }

    public function refund(Payment $paymentModel, int $amount): PaymentRefund
    {
        return new PaymentRefund(success: false);
    }

    public function capture(Payment $paymentModel, array $data): PaymentCapture
    {
        return match ($data['status']) {
            'success' => $this->processTransaction($paymentModel, $data),
            'cancelled' => $this->cancelTransaction($paymentModel),
            default => throw new InvalidArgumentException(),
        };
    }

    protected function processTransaction(Payment $paymentModel, array $data): PaymentCapture
    {

        $this->visionPayClient->authenticate();

        $response = json_decode(
            $this->visionPayClient->getPaymentListByReference($data['reference'])
                ->body(), true
        );

        if ($response['reference'] == $data['reference'] && $response['approvalCode'] == $data['authcode']) {

            $paymentModel->update([
                'status' => PaymentStatus::PAID->value,
            ]);

            event(new PaymentProcessEvent($paymentModel));

            return new PaymentCapture(success: true);
        }

        throw new InvalidArgumentException('Invalid Payment Data');
    }

    protected function cancelTransaction(Payment $paymentModel): PaymentCapture
    {

        $paymentModel->update([
            'status' => PaymentStatus::CANCELLED->value,
        ]);

        event(new PaymentProcessEvent($paymentModel));

        return new PaymentCapture(
            success: false,
            message: 'The request for payment has been cancelled.'
        );

    }
}
