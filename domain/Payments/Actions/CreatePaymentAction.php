<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use DB;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\Enums\PaymentStatus;
use Domain\Payments\Interfaces\PayableInterface;
use Throwable;

class CreatePaymentAction
{
    public function __construct(
        public readonly CreatePaymentLink $createPaymentlink
    ) {}

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
                'status' => PaymentStatus::PENDING->value,
            ]);

            DB::commit();

            return $this->createPaymentlink->execute(
                $paymentTransaction,
                $paymentData
            );

        } catch (Throwable $th) {

            DB::rollBack();

            throw $th;
        }

    }
}
