<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\Enums\OrderAddressTypes;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderAddress;

class GuestCreateOrderAddressAction
{
    public function execute(Order $order, GuestPreparedOrderData $guestPreparedOrderData): void
    {
        $addressesToInsert = [
            [
                'order_id' => $order->id,
                'type' => OrderAddressTypes::SHIPPING,
                'country' => $guestPreparedOrderData->countries->shippingCountry->name,
                'state' => $guestPreparedOrderData->states->shippingState->name,
                'label_as' => $guestPreparedOrderData->shippingAddress->label_as,
                'address_line_1' => $guestPreparedOrderData->shippingAddress->address_line_1,
                'zip_code' => $guestPreparedOrderData->shippingAddress->zip_code,
                'city' => $guestPreparedOrderData->shippingAddress->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'type' => OrderAddressTypes::BILLING,
                'country' => $guestPreparedOrderData->countries->billingCountry->name,
                'state' => $guestPreparedOrderData->states->billingState->name,
                'label_as' => $guestPreparedOrderData->billingAddress->label_as,
                'address_line_1' => $guestPreparedOrderData->billingAddress->address_line_1,
                'zip_code' => $guestPreparedOrderData->billingAddress->zip_code,
                'city' => $guestPreparedOrderData->billingAddress->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        OrderAddress::insert($addressesToInsert);
    }
}
