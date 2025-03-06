<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Cart\Models\CartLine;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\Events\PublicOrder\GuestOrderPlacedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\Actions\CreatePaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Exceptions\PaymentException;
use Exception;

readonly class GuestSplitOrderAction
{
    public function __construct(
        private GuestCreateOrderAction $guestCreateOrderAction,
        private GuestCreateOrderLineAction $guestCreateOrderLineAction,
        private GuestCreateOrderAddressAction $guestCreateOrderAddressAction,
        private CreatePaymentAction $createPaymentAction,
    ) {}

    public function execute(GuestPreparedOrderData $guestPreparedOrderData, GuestPlaceOrderData $guestPlaceOrderData): array|Exception
    {
        $order = $this->guestCreateOrderAction
            ->execute($guestPlaceOrderData, $guestPreparedOrderData);

        $this->guestCreateOrderLineAction
            ->execute($order, $guestPlaceOrderData, $guestPreparedOrderData);

        $this->guestCreateOrderAddressAction
            ->execute($order, $guestPreparedOrderData);

        CartLine::whereCheckoutReference($guestPlaceOrderData->cart_reference)
            ->update(['checked_out_at' => now()]);

        $payment = $this->proceedPayment($order, $guestPreparedOrderData);

        event(new GuestOrderPlacedEvent(
            $order,
            $guestPreparedOrderData,
            $guestPlaceOrderData
        ));

        return [
            'order' => $order,
            'payment' => $payment,
        ];
    }

    private function proceedPayment(Order $order, GuestPreparedOrderData $guestPreparedOrderData): PaymentAuthorize
    {
        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $order->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $guestPreparedOrderData->currency->code,
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
            payment_driver: $guestPreparedOrderData->paymentMethod->slug
        );

        $result = $this->createPaymentAction
            ->execute($order, $providerData);

        if ($result->success) {
            return $result;
        }

        throw new PaymentException;
    }
}
