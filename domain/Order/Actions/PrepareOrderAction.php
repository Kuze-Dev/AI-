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
        $customerId = auth()->user()?->id;

        $customer = Customer::find($customerId);

        $shippingAddress = Address::find($placeOrderData->addresses->shipping);

        $billingAddress = Address::find($placeOrderData->addresses->billing);

        $currency = Currency::where('enabled', true)->first();

        $totals = CartLine::whereIn('id', $placeOrderData->cart_line_ids)
            ->with(['purchasable', 'variant'])
            ->get()
            ->reduce(function ($totals, $cartLine) {
                $variant = $cartLine->variant;
                $product = $cartLine->purchasable;

                if ($variant) {
                    $totals['sub_total'] += (float) $variant->selling_price * $cartLine->quantity;
                } else {
                    $totals['sub_total'] += $product->selling_price * $cartLine->quantity;
                }

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

        return $orderData;

        return PreparedOrderData::fromArray($orderData);
    }
}
