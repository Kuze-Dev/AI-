<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Address\Models\Address;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;

class PrepareOrderAction
{
    public function execute(PlaceOrderData $placeOrderData)
    {
        $customer = auth()->user();

        $shippingAddress = Address::find($placeOrderData->addresses->shipping);

        $billingAddress = Address::find($placeOrderData->addresses->billing);

        $currency = Currency::where('default', true)->first();

        $totals = CartLine::with(['purchasable'])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get()
            ->reduce(function ($totals, $cartLine) {
                $purchasable = $cartLine->purchasable;

                $totals['sub_total'] += $purchasable->selling_price * $cartLine->quantity;

                return $totals;
            }, [
                'sub_total' => 0,
                'shipping_total' => 0,
            ]);

        $notes = $placeOrderData->notes;

        $orderData =  [
            'customer' => $customer,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'currency' => $currency,
            'totals' => $totals,
            'notes' => $notes,
        ];

        // return $orderData;

        return PreparedOrderData::fromArray($orderData);
    }
}
