<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Address\Models\Address;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Domain\Taxation\Facades\Taxation;

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
        },])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get();

        $taxZone = Taxation::getTaxZone($placeOrderData->taxation_data->country_id, $placeOrderData->taxation_data->state_id);

        $notes = $placeOrderData->notes;

        $discount = Discount::whereCode($placeOrderData->discountCode)
            ->whereStatus(DiscountStatus::ACTIVE)
            ->where(function ($query) {
                $query->where('max_uses', '>', 0)
                    ->orWhereNull('max_uses');
            })
            ->firstOrFail();

        $orderData = [
            'customer' => $customer,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'currency' => $currency,
            'cartLine' => $cartLines,
            'notes' => $notes,
            'taxZone' => $taxZone,
            'discount' => $discount,
        ];

        return PreparedOrderData::fromArray($orderData);
    }
}
