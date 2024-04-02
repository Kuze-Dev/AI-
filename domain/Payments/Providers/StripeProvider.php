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
use InvalidArgumentException;
use Stripe\StripeClient;
use Throwable;

class StripeProvider extends Provider
{
    protected string $name = 'stripe';

    private StripeClient $stripeClient;

    public function __construct()
    {
        $paymentSettings = app(PaymentSettings::class);

        if ($paymentSettings->stripe_secret_key) {
            $this->stripeClient = new StripeClient($paymentSettings->stripe_secret_key);
        }
    }

    #[\Override]
    public function authorize(): PaymentAuthorize
    {

        try {
            $providerData = $this->data;

            $transaction = $providerData->transactionData;

            $session = $this->stripeClient->checkout->sessions->create([
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $transaction->getCurrency(),
                            'product_data' => [
                                'name' => 'Payment For '.$transaction->reference_id,
                            ],
                            'unit_amount' => $transaction->amount->total,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
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
            ]);

            $paymentModel = $providerData->paymentModel;

            $paymentModel->update([
                'payment_id' => $session->id,
            ]);

            return PaymentAuthorize::fromArray([
                'success' => true,
                'url' => $session->url,
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

        try {

            $refund = $this->stripeClient->refunds->create([
                'payment_intent' => $paymentModel->transaction_id,
                'amount' => $amount * 100,
            ]);

            if ($refund->status == 'succeeded') {

                $paymentModel->refunds()->create([

                    'refund_id' => $refund->id,
                    'amount' => $amount,
                    'status' => 'success',
                    'transaction_id' => $refund->balance_transaction,
                    'refund_details' => $refund->toArray(),
                ]);

                $refunded_amount = $paymentModel->refunds->sum('amount');

                $paymentModel->update([
                    'status' => ($amount == $refunded_amount) ? PaymentStatus::REFUNDED : PaymentStatus::PARTIALLY_REFUNDED,
                ]);

                return new PaymentRefund(
                    success: true,
                    message: 'Total Refunded Amount '.$refunded_amount
                );
            }

            return new PaymentRefund(success: false);

        } catch (Throwable $th) {

            return new PaymentRefund(
                success: false,
                message: $th->getMessage()
            );
        }

    }

    #[\Override]
    public function capture(Payment $paymentModel, array $data): PaymentCapture
    {
        return match ($data['status']) {
            'success' => $this->processTransaction($paymentModel, $data),
            'cancelled' => $this->cancelTransaction($paymentModel),
            default => throw new InvalidArgumentException(),
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
        if ($paymentModel->payment_id) {

            $paymentDetails = $this->stripeClient->checkout
                ->sessions->retrieve($paymentModel->payment_id);

            $paymentModel->update([
                'transaction_id' => $paymentDetails->payment_intent,
                'status' => PaymentStatus::PAID->value,
            ]);

            event(new PaymentProcessEvent($paymentModel));

            return new PaymentCapture(success: true);
        }

        return new PaymentCapture(
            success: false,
            message: 'Cant Retreive payment details'
        );

    }
}
