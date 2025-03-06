<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;

readonly class CreateOrderAction
{
    public function __construct(
        private CartSummaryAction $cartSummaryAction,
        private CreateOrderReference $createOrderReference
    ) {}

    public function execute(PlaceOrderData $placeOrderData, PreparedOrderData $preparedOrderData): Order
    {
        $referenceNumber = $this->createOrderReference->execute();

        /** @var \Domain\Address\Models\State $state */
        $state = $preparedOrderData->billingAddress->state;

        /** @var \Domain\Address\Models\Country $country */
        $country = $state->country;

        $summary = $this->cartSummaryAction->execute(
            $preparedOrderData->cartLine,
            new CartSummaryTaxData(
                $country->id,
                $state->id,
            ),
            new CartSummaryShippingData(
                $preparedOrderData->customer,
                $preparedOrderData->shippingAddress,
                $preparedOrderData->shippingMethod
            ),
            $preparedOrderData->discount,
            $placeOrderData->serviceId
        );

        $paymentMethod = $preparedOrderData->paymentMethod->gateway == 'manual'
            ? OrderStatuses::PENDING : OrderStatuses::FORPAYMENT;

        $order = Order::create([
            'customer_id' => $preparedOrderData->customer->id,
            'customer_first_name' => $preparedOrderData->customer->first_name,
            'customer_last_name' => $preparedOrderData->customer->last_name,
            'customer_mobile' => $preparedOrderData->customer->mobile,
            'customer_email' => $preparedOrderData->customer->email,

            'currency_code' => $preparedOrderData->currency->code,
            'currency_name' => $preparedOrderData->currency->name,
            'currency_symbol' => $preparedOrderData->currency->symbol,

            'reference' => $referenceNumber,

            'tax_total' => $summary->taxTotal,
            'tax_display' => $summary->taxDisplay,
            'tax_percentage' => $summary->taxPercentage,

            'sub_total' => $summary->initialSubTotal,

            'discount_total' => $summary->discountTotal,
            'discount_id' => $preparedOrderData->discount ? $preparedOrderData->discount->id : null,
            'discount_code' => $preparedOrderData->discount ? $preparedOrderData->discount->code : null,

            'shipping_total' => $summary->initialShippingTotal,
            'shipping_method_id' => $preparedOrderData->shippingMethod->id,

            'total' => $summary->grandTotal,

            'notes' => $preparedOrderData->notes,
            'status' => $paymentMethod,
            'is_paid' => false,
        ]);

        return $order;
    }
}
