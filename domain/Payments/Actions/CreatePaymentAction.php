<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\ProviderData;
use Domain\Payments\Interfaces\PayableInterface;
use Throwable;
use DB;

class CreatePaymentAction
{
    /** Execute create collection query. */
    public function execute(PayableInterface $model, CreatepaymentData $paymentData): PaymentAuthorize
    {
        try {

            DB::beginTransaction();

            $paymentMethod = PaymentMethod::where('slug', $paymentData->payment_driver)->firstorFail();

            $paymentTransaction = $model->payments()->create([
                'payment_method_id' => $paymentMethod->id,
                'gateway' => $paymentMethod->gateway,
                'currency' => $paymentData->transactionData->getCurrency(),
                'amount' => $paymentData->transactionData->getTotal(),
                'payment_details' => $paymentData->transactionData->getPaymentDetails(),
                'status' => 'pending',
            ]);

            $providerData = new ProviderData(
                transactionData: $paymentData->transactionData,
                paymentModel: $paymentTransaction,
                payment_method_id: $paymentMethod->id,
            );

            $result = app(PaymentManagerInterface::class)
                ->driver($paymentMethod->slug)
                ->withData($providerData)
                ->authorize();

            DB::commit();

            return $result;

        } catch (Throwable $th) {

            DB::rollBack();

            throw $th;
        }

    }
}
