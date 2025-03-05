<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Cart\Actions\PublicCart\GuestCartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\GuestCartSummaryShippingData;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Illuminate\Support\Str;

readonly class GuestCreateOrderAction
{
    public function __construct(
        private GuestCartSummaryAction $guestCartSummaryAction,
    ) {
    }

    public function execute(GuestPlaceOrderData $guestPlaceOrderData, GuestPreparedOrderData $guestPreparedOrderData): Order
    {
        $referenceNumber = Str::upper(Str::random(12));

        $summary = $this->guestCartSummaryAction->execute(
            $guestPreparedOrderData->cartLine,
            new CartSummaryTaxData(
                $guestPreparedOrderData->countries->billingCountry->id,
                $guestPreparedOrderData->states->billingState->id,
            ),
            new GuestCartSummaryShippingData(
                $guestPreparedOrderData->shippingReceiverData,
                $guestPreparedOrderData->shippingAddressData,
                $guestPreparedOrderData->shippingMethod
            ),
            $guestPreparedOrderData->discount,
            $guestPlaceOrderData->serviceId
        );

        $paymentMethod = $guestPreparedOrderData->paymentMethod->gateway == 'manual'
            ? OrderStatuses::PENDING : OrderStatuses::FORPAYMENT;

        $order = Order::create([
            'customer_id' => null,
            'customer_first_name' => $guestPreparedOrderData->customer->first_name,
            'customer_last_name' => $guestPreparedOrderData->customer->last_name,
            'customer_mobile' => $guestPreparedOrderData->customer->mobile,
            'customer_email' => $guestPreparedOrderData->customer->email,

            'currency_code' => $guestPreparedOrderData->currency->code,
            'currency_name' => $guestPreparedOrderData->currency->name,
            'currency_symbol' => $guestPreparedOrderData->currency->symbol,

            'reference' => $referenceNumber,

            'tax_total' => $summary->taxTotal,
            'tax_display' => $summary->taxDisplay,
            'tax_percentage' => $summary->taxPercentage,

            'sub_total' => $summary->initialSubTotal,

            'discount_total' => $summary->discountTotal,
            'discount_id' => $guestPreparedOrderData->discount ? $guestPreparedOrderData->discount->id : null,
            'discount_code' => $guestPreparedOrderData->discount ? $guestPreparedOrderData->discount->code : null,

            'shipping_total' => $summary->initialShippingTotal,
            'shipping_method_id' => $guestPreparedOrderData->shippingMethod->id,

            'total' => $summary->grandTotal,

            'notes' => $guestPreparedOrderData->notes,
            'status' => $paymentMethod,
            'is_paid' => false,
        ]);

        return $order;
    }
}
