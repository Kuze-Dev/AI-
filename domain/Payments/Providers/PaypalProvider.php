<?php

declare(strict_types=1);

namespace Domain\Payments\Providers;

use App\Settings\PaymentSettings;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentRefund;
use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Models\Payment as ModelsPayment;
use Throwable;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalProvider extends Provider
{
    protected string $name = 'paypal';

    private PayPalClient $payPalclient;

    public function __construct()
    {
        /** @var array */
        $paypalCredentials = app(PaymentSettings::class)->paypal_credentials;

        $config = [
            'mode' => app(PaymentSettings::class)->paypal_mode ? 'live' : 'sandbox',
            'live' => [
                'client_id' => $paypalCredentials['paypal_secret_id'],
                'client_secret' => $paypalCredentials['paypal_secret_key'],
                'app_id' => '',
            ],
            'sandbox' => [
                'client_id' => $paypalCredentials['paypal_secret_id'],
                'client_secret' => $paypalCredentials['paypal_secret_key'],
                'app_id' => '',
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            'notify_url' => '', //?
            'locale' => 'en_US',
            'validate_ssl' => true,
        ];

        $this->payPalclient = new PaypalClient($config);

        $this->payPalclient->getAccessToken();

    }

    public function authorize(): PaymentAuthorize
    {

        try {

            $providerData = $this->data;

            $paymentData = $providerData->paymentModel;

            $request = [
                'intent' => 'CAPTURE',
                'application_context' => [
                    'return_url' => route(
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
                            'status' => 'cancel',  ]
                    ),

                ],
                'purchase_units' => [
                    0 => [
                        'amount' => [
                            'currency_code' => $paymentData->currency,
                            'value' => $paymentData->amount,
                        ],
                    ],

                ],

            ];

            /** @var array */
            $order = $this->payPalclient->createOrder($request);

            $paymentData->update([
                'payment_id' => $order['id'],
            ]);

            $redirectUrl = array_reduce($order['links'], function ($result, $link) {
                if ($link['rel'] === 'approve') {
                    return $link;
                }

                return $result;
            }, [])['href'];

            return PaymentAuthorize::fromArray([
                'success' => true,
                'url' => $redirectUrl,
            ]);

        } catch (Throwable $th) {

            return PaymentAuthorize::fromArray([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }

    }

    public function capture(ModelsPayment $paymentModel, array $data): PaymentCapture
    {
        return match ($data['status']) {
            'success' => $this->processTransaction($paymentModel, $data),
            'cancel' => $this->cancelTransaction($paymentModel),
            default => throw new InvalidArgument(),
        };
    }

    protected function processTransaction(ModelsPayment $paymentModel, array $data): PaymentCapture
    {
        /** @var array */
        $captured = $this->payPalclient->capturePaymentOrder($data['token']);

        $paymentModel->update([
            'status' => 'paid',
            'transaction_id' => $captured['purchase_units']['0']['payments']['captures']['0']['id'],
        ]);

        event(new PaymentProcessEvent($paymentModel));

        return new PaymentCapture(success: true);

    }

    protected function cancelTransaction(ModelsPayment $paymentModel): PaymentCapture
    {

        $paymentModel->update([
            'status' => 'cancel',
        ]);

        event(new PaymentProcessEvent($paymentModel));

        return new PaymentCapture(
            success: false,
            message: 'The request for payment has been canceled.'
        );

    }

    public function refund(): PaymentRefund
    {
        return new PaymentRefund(success: false);
    }
}
