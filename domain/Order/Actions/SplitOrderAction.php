<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\Models\CartLine;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\Actions\CreatePaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Exceptions\PaymentException;

readonly class SplitOrderAction
{
    public function __construct(
        private CreateOrderAction $createOrderAction,
        private CreateOrderLineAction $createOrderLineAction,
        private CreateOrderAddressAction $createOrderAddressAction,
        private CreatePaymentAction $createPaymentAction,
    ) {
    }

    public function execute(PreparedOrderData $preparedOrderData, PlaceOrderData $placeOrderData): array
    {
        $order = $this->createOrderAction
            ->execute($placeOrderData, $preparedOrderData);

        $this->createOrderLineAction
            ->execute($order, $placeOrderData, $preparedOrderData);

        $this->createOrderAddressAction
            ->execute($order, $preparedOrderData);

        CartLine::whereCheckoutReference($placeOrderData->cart_reference)
            ->update(['checked_out_at' => now()]);

        $payment = $this->proceedPayment($order, $preparedOrderData);

        event(new OrderPlacedEvent(
            $order,
            $preparedOrderData,
            $placeOrderData
        ));

        return [
            'order' => $order,
            'payment' => $payment,
        ];
    }

    private function proceedPayment(Order $order, PreparedOrderData $preparedOrderData): PaymentAuthorize
    {
        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $order->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $preparedOrderData->currency->code,
                        'total' => $order->total,
                        'details' => PaymentDetailsData::fromArray(
                            [
                                'subtotal' => strval($order->sub_total - $order->discount_total),
                                'tax' => strval($order->tax_total),
                            ]
                        ),
                    ]),
                ]
            ),
            payment_driver: $preparedOrderData->paymentMethod->slug
        );

        $result = $this->createPaymentAction
            ->execute($order, $providerData);

        if ($result->success) {
            return $result;
        }

        throw new PaymentException();
    }
}
