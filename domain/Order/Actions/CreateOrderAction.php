<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Discount\Actions\CreateDiscountLimitAction;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function execute(PreparedOrderData $preparedOrderData)
    {
        $referenceNumber = Str::upper(Str::random(12));

        // dd($deductable_subtotal_amount);
        $subTotal = $preparedOrderData->cartLine->reduce(function ($carry, $cartLine) {
            $purchasable = $cartLine->purchasable;

            return $carry + ($purchasable->selling_price * $cartLine->quantity);
        }, 0);

        $deductable_subtotal_amount = (new DiscountHelperFunctions())->deductOrderSubtotalByFixedValue($preparedOrderData->discountCode)
                    ?: (new DiscountHelperFunctions())->deductOrderSubtotalByPercentageValue($preparedOrderData->discountCode, $subTotal);

        $total = $subTotal - ($deductable_subtotal_amount !== null ? $deductable_subtotal_amount : 0);
        //add tax and minus discount here
        // $total = $subTotal + $preparedOrderData->totals->shipping_total;

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

            'tax_total' => 0,
            'sub_total' => $subTotal,
            'discount_total' => $deductable_subtotal_amount ?? 0,
            'shipping_total' => 0,
            'total' => $total,

            'notes' => $preparedOrderData->notes,
            'shipping_method' => 'test shipping_method',
            'shipping_details' => 'test shipping details',
            'payment_method' => 'COD',
            'payment_details' => 'test payment details',
            'is_paid' => false,
        ]);
        app(CreateDiscountLimitAction::class)->execute($preparedOrderData->discountCode, $order, $preparedOrderData->customer);

        return $order;
    }
}
