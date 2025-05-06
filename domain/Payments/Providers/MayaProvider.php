<?php

declare(strict_types=1);

namespace Domain\Payments\Providers;

use App\Settings\PaymentSettings;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentRefund;
use Domain\Payments\Enums\PaymentStatus;
use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Models\Payment;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Lloricode\Paymaya\PaymayaClient;
use Lloricode\Paymaya\Request\Checkout\Amount\AmountDetail;
use Lloricode\Paymaya\Request\Checkout\Checkout;
use Lloricode\Paymaya\Request\Checkout\RedirectUrl;
use Lloricode\Paymaya\Request\Checkout\TotalAmount;
use PaymayaSDK;
use Throwable;

class MayaProvider extends Provider
{
    protected string $name = 'maya';

    protected string $secretKey;

    protected string $baseUrl;

    public function __construct()
    {
        $paymentSettings = app(PaymentSettings::class);

        $this->secretKey = (string) $paymentSettings->maya_secret_key;

        $mode = $paymentSettings->maya_production_mode
            ? PaymayaClient::ENVIRONMENT_PRODUCTION
            : PaymayaClient::ENVIRONMENT_SANDBOX;

        $this->baseUrl = $paymentSettings->maya_production_mode
            ? 'https://pg.paymaya.com'
            : 'https://pg-sandbox.paymaya.com';

        config([
            'paymaya-sdk.mode' => $mode,
            'paymaya-sdk.keys.public' => $paymentSettings->maya_publishable_key,
            'paymaya-sdk.keys.secret' => $paymentSettings->maya_secret_key,
        ]);
    }

    #[\Override]
    public function authorize(): PaymentAuthorize
    {
        try {
            $providerData = $this->data;
            $transaction = $providerData->transactionData;

            $checkout = (new Checkout)
                ->setTotalAmount(
                    (new TotalAmount)
                        ->setValue($transaction->amount->total / 100)
                        ->setDetails(
                            (new AmountDetail)->setSubtotal($transaction->amount->total / 100)
                        )
                )
                ->setRedirectUrl(
                    (new RedirectUrl)
                        ->setSuccess(route('tenant.api.payment-callback', [
                            'paymentmethod' => $providerData->payment_method_id,
                            'transactionId' => $providerData->paymentModel->id,
                            'status' => 'success',
                        ]))
                        ->setFailure(route('tenant.api.payment-callback', [
                            'paymentmethod' => $providerData->payment_method_id,
                            'transactionId' => $providerData->paymentModel->id,
                            'status' => 'cancelled',
                        ]))
                        ->setCancel(route('tenant.api.payment-callback', [
                            'paymentmethod' => $providerData->payment_method_id,
                            'transactionId' => $providerData->paymentModel->id,
                            'status' => 'cancelled',
                        ]))
                )
                ->setRequestReferenceNumber($transaction->reference_id);

            $checkoutResponse = PaymayaSDK::checkout()->execute($checkout);

            if (! isset($checkoutResponse->checkoutId) || ! isset($checkoutResponse->redirectUrl)) {
                throw new \Exception('Invalid response from PayMaya Checkout API.');
            }

            $paymentModel = $providerData->paymentModel;
            $paymentModel->update([
                'payment_id' => $checkoutResponse->checkoutId,
            ]);

            return PaymentAuthorize::fromArray([
                'success' => true,
                'url' => $checkoutResponse->redirectUrl,
            ]);

        } catch (Throwable $th) {

            return PaymentAuthorize::fromArray([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    #[\Override]
    public function refund(Payment $paymentModel, int $amount): PaymentRefund
    {
        $refund = [
            'reason' => 'Customer requested refund',
            'totalAmount' => [
                'currency' => 'PHP',
                'amount' => $amount,
            ],
        ];

        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl.'/payments/v1/payments/'.$paymentModel->payment_id.'/refunds', $refund);

        $refundResponse = $response->json();

        if (($refundResponse['status'] ?? null) === 'SUCCESS') {
            $paymentModel->refunds()->create([
                'refund_id' => $refundResponse['id'] ?? null,
                'amount' => $amount,
                'status' => 'success',
                'transaction_id' => $paymentModel->transaction_id,
                'refund_details' => $refundResponse,
            ]);

            $paymentModel->update([
                'status' => PaymentStatus::REFUNDED->value,
            ]);

            $refunded_amount = $paymentModel->refunds->sum('amount');

            $paymentModel->update([
                'status' => ($amount === $refunded_amount) ? PaymentStatus::REFUNDED : PaymentStatus::PARTIALLY_REFUNDED,
            ]);

            event(new PaymentProcessEvent($paymentModel));

            return new PaymentRefund(success: true, message: 'Refund successful.');
        }

        return new PaymentRefund(
            success: false,
            message: 'Refund failed: '.($refundResponse['message'] ?? $response->body())
        );
    }

    #[\Override]
    public function capture(Payment $paymentModel, array $data): PaymentCapture
    {
        return match ($data['status']) {
            'success' => $this->processTransaction($paymentModel, $data),
            'cancelled' => $this->cancelTransaction($paymentModel),
            default => throw new InvalidArgumentException,
        };
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

    protected function processTransaction(Payment $paymentModel, array $data): PaymentCapture
    {
        try {
            if ($paymentModel->payment_id) {
                $response = Http::withBasicAuth($this->secretKey, '')
                    ->get($this->baseUrl.'/payments/v1/payments/'.$paymentModel->payment_id);

                $paymentDetails = $response->json();

                if ($paymentDetails['status'] === 'PAYMENT_SUCCESS') {
                    $transaction_id = $paymentDetails['receipt']['transactionId'];
                    $paymentModel->update([
                        'transaction_id' => $transaction_id,
                        'status' => PaymentStatus::PAID->value,
                    ]);

                    event(new PaymentProcessEvent($paymentModel));

                    return new PaymentCapture(success: true);
                }
            }

            return new PaymentCapture(
                success: false,
                message: 'Cannot retrieve payment details'
            );
        } catch (Throwable $th) {
            return new PaymentCapture(
                success: false,
                message: $th->getMessage()
            );
        }
    }
}
