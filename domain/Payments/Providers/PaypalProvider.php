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
use Domain\Payments\Providers\Concerns\HandlesRedirection as ConcernsHandlesRedirection;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Throwable;

class PaypalProvider extends Provider
{
    // use ConcernsHandlesRedirection;

    protected string $name = 'paypal';

    /** @var \PayPal\Rest\ApiContext */
    private $paypalApiContext;

    public function __construct()
    {
        /** @var array */
        $paypalCredentials = app(PaymentSettings::class)->paypal_credentials;

        $this->paypalApiContext = new ApiContext(
            new OAuthTokenCredential(
                clientId: $paypalCredentials['paypal_secret_id'],
                clientSecret: $paypalCredentials['paypal_secret_key']
            )
        );

        $this->setConfig(
            array_merge(
                config('payment-gateway.paypal'),
                [
                    'mode' => app(PaymentSettings::class)->paypal_mode ? 'live' : 'sandbox',
                ]
            )
        );

        $this->paypalApiContext->setConfig($this->config);
    }

    public function authorize(): PaymentAuthorize
    {

        try {

            $providerData = $this->data;

            $payer = app(Payer::class)->setPaymentMethod($this->name);
            // (['payment_method' => $this->name]);

            // $totalItems = $providerData->transactionData->item_list ?
            //         count($providerData->transactionData->item_list) :
            //         0;
            // if ($totalItems > 0) {

            //     $itemList = new ItemList();

            //     foreach ($providerData->transactionData->item_list as $item) {
            //         $itemList->addItem(new Item([
            //             'name' => $item->name,
            //             'quantity' => $item->quantity,
            //             'currency' => $item->currency,
            //             'price' => $item->price,
            //         ]));
            //     }
            // }

            $paymentData = $providerData->paymentModel;

            /** @phpstan-ignore-next-line */
            $amount = new Amount([
                'currency' => $paymentData->currency,
                'total' => $paymentData->amount,
                /** @phpstan-ignore-next-line */
                'details' => new Details($paymentData->payment_details),
            ]);

            $transaction = app(Transaction::class)
                ->setAmount($amount);
            // ->setItemList($itemList ?? [])
            // ->setDescription((string) $providerData->transactionData->description);

            $redirectUrls = app(RedirectUrls::class)
                ->setReturnUrl(
                    route('tenant.api.payment-callback', [
                        'paymentmethod' => $providerData->payment_method_id,
                        'transactionId' => $providerData->paymentModel->id,
                        'status' => 'success',
                    ])
                )->setCancelUrl(
                    route('tenant.api.payment-callback', [
                        'paymentmethod' => $providerData->payment_method_id,
                        'transactionId' => $providerData->paymentModel->id,
                        'status' => 'cancel',
                    ])
                );

            $payment = app(Payment::class)->setIntent('sale')
                ->setPayer($payer)
                ->addTransaction($transaction)
                ->setRedirectUrls($redirectUrls);

            $payment->create($this->paypalApiContext);

            return PaymentAuthorize::fromArray([
                'success' => true,
                'url' => $payment->getApprovalLink(),
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
            default => throw new InvalidArgument(),
        };
    }

    protected function processTransaction(ModelsPayment $paymentModel, array $data): PaymentCapture
    {
        /** @var Payment */
        $payment = Payment::get($data['paymentId'], $this->paypalApiContext);

        $execution = app(PaymentExecution::class)->setPayerId($data['PayerID']);

        $payment->execute($execution, $this->paypalApiContext);

        /** @phpstan-ignore-next-line */
        $transaction = $payment->transactions['0'];

        $paymentModel->update([
            'status' => 'paid',
            'payment_id' => $data['paymentId'],
            'transaction_id' => $transaction->getRelatedResources()['0']->getSale()->getId(),
        ]);

        event(new PaymentProcessEvent($paymentModel));

        return new PaymentCapture(success: true);

    }

    public function refund(): PaymentRefund
    {
        return new PaymentRefund(success: false);
    }
}
