<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Actions\CreatePaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Exceptions\PaymentException;
use Domain\ServiceOrder\Models\ServiceBill;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CheckoutServiceOrderAction
{
    public function __construct(
        private CreateServiceTransactionAction $createServiceTransactionAction,
        private CreatePaymentAction $createPaymentAction,
    ) {
    }

    public function execute(array $data): array
    {
        $paymentMethod = $this->preparePaymentMethod($data['payment_method']);

        $serviceBill = ServiceBill::whereReference($data['reference_id'])
            ->firstOrFail();

        $payment = $this->proceedPayment($serviceBill, $paymentMethod);

        $this->createServiceTransactionAction->execute($data, $paymentMethod);

        return [
            $payment,
            $serviceBill,
        ];
    }

    private function preparePaymentMethod(string $payment_method): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($payment_method)->first();

        if ( ! $paymentMethod instanceof PaymentMethod) {
            throw new BadRequestHttpException('No paymentMethod found');
        }

        return $paymentMethod;
    }

    private function proceedPayment(
        ServiceBill $serviceBill,
        PaymentMethod $paymentMethod
    ): PaymentAuthorize {
        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $serviceBill->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $serviceBill->serviceOrder->currency_code,
                        'total' => (int) $serviceBill->total_amount,
                        'details' => PaymentDetailsData::fromArray(
                            []
                        ),
                    ]),
                ]
            ),
            payment_driver: $paymentMethod->slug
        );

        $result = $this->createPaymentAction
            ->execute($serviceBill, $providerData);

        if ($result->success) {
            return $result;
        }

        throw new PaymentException();
    }
}
