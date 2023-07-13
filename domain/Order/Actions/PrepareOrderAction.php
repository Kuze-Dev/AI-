<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Address\Models\Address;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PrepareOrderAction
{
    public function execute(PlaceOrderData $placeOrderData)
    {
        $customer = auth()->user();

        $shippingAddress = Address::with('state.country')->find($placeOrderData->addresses->shipping);

        $billingAddress = Address::with('state.country')->find($placeOrderData->addresses->billing);

        $currency = Currency::where('default', true)->first();

        $cartLines = CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product'],
            ]);
        }, ])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get();

        $notes = $placeOrderData->notes;

        $discountCode = $placeOrderData->discountCode;

        $orderData = [
            'customer' => $customer,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'currency' => $currency,
            'cartLine' => $cartLines,
            'notes' => $notes,
            'discountCode' => $discountCode,
        ];

        return PreparedOrderData::fromArray($orderData);
    }
}
