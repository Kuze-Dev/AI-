<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\Models\CartLine;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Domain\Payments\Actions\CreatePaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\TransactionData;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SplitOrderAction
{
    public function execute(PreparedOrderData $preparedOrderData, PlaceOrderData $placeOrderData)
    {
        return DB::transaction(function () use ($preparedOrderData, $placeOrderData) {
            try {
                DB::beginTransaction();

                $order = app(CreateOrderAction::class)
                    ->execute($preparedOrderData);

                app(CreateOrderLineAction::class)
                    ->execute($order, $preparedOrderData);

                app(CreateOrderAddressAction::class)
                    ->execute($order, $preparedOrderData);

                CartLine::whereCheckoutReference($placeOrderData->cart_reference)
                    ->update(['checked_out_at' => now()]);

                $payment = $this->proceedPayment($order, $preparedOrderData) ?? null;

                DB::commit();

                return [
                    'order' => $order,
                    'payment' => $payment,
                ];
            } catch (Exception $e) {
                DB::rollBack();
                Log::info('Error on SplitOrderAction->execute() ' . $e);

                return $e;
            }
        });
    }

    private function proceedPayment(Order $order, PreparedOrderData $preparedOrderData)
    {
        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $order->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $preparedOrderData->currency->code,
                        'total' => strval($order->total),
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

        $result = app(CreatePaymentAction::class)
            ->execute($order, $providerData);

        return $result;
    }
}
