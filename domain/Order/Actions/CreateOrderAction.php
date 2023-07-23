<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\Helpers\CartLineHelper;
use Domain\Discount\Actions\CreateDiscountLimitAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function execute(PlaceOrderData $placeOrderData, PreparedOrderData $preparedOrderData)
    {
        $referenceNumber = Str::upper(Str::random(12));

        $summary = app(CartLineHelper::class)->getSummary(
            $preparedOrderData->cartLine,
            new CartSummaryTaxData(
                $placeOrderData->taxation_data->country_id,
                $placeOrderData->taxation_data->state_id
            ),
            new CartSummaryShippingData(
                $preparedOrderData->customer,
                $preparedOrderData->shippingAddress,
                $preparedOrderData->shippingMethod
            ),
            $preparedOrderData->discount,
        );

        $order = Order::create([
            'customer_id' => $preparedOrderData->customer->id,
            'customer_first_name' => $preparedOrderData->customer->first_name,
            'customer_last_name' => $preparedOrderData->customer->last_name,
            'customer_mobile' => $preparedOrderData->customer->mobile,
            'customer_email' => $preparedOrderData->customer->email,

            'currency_code' => $preparedOrderData->currency->code,
            'currency_name' => $preparedOrderData->currency->name,
            'currency_exchange_rate' => $preparedOrderData->currency->exchange_rate,

            'reference' => $referenceNumber,

            'tax_total' => $summary->taxTotal,
            'tax_display' => $summary->taxDisplay,
            'tax_percentage' => $summary->taxPercentage,

            'sub_total' => $summary->subTotal,

            'discount_total' => $summary->discountTotal,
            'discount_id' => $preparedOrderData->discount ? $preparedOrderData->discount->id : null,
            'discount_code' => $preparedOrderData->discount ? $preparedOrderData->discount->code : null,

            'shipping_total' => $summary->shippingTotal,
            'total' => $summary->grandTotal,

            'notes' => $preparedOrderData->notes,
            'shipping_method' => 'test shipping_method',
            'shipping_details' => 'test shipping details',

            'is_paid' => false,
        ]);

        if ( ! is_null($preparedOrderData->discount)) {
            app(CreateDiscountLimitAction::class)->execute($preparedOrderData->discount, $order, $preparedOrderData->customer);
        }

        return $order;
    }
}
