<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Enums\OrderAddressTypes;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderAddress;

class CreateOrderAddressAction
{
    public function execute(Order $order, PreparedOrderData $preparedOrderData): void
    {
        /** @var \Domain\Address\Models\State $shippingState */
        $shippingState = $preparedOrderData->shippingAddress->state;
        /** @var \Domain\Address\Models\Country $shippingCountry */
        $shippingCountry = $shippingState->country;

        /** @var \Domain\Address\Models\State $billingState */
        $billingState = $preparedOrderData->billingAddress->state;
        /** @var \Domain\Address\Models\Country $billingCountry */
        $billingCountry = $billingState->country;

        $addressesToInsert = [
            [
                'order_id' => $order->id,
                'type' => OrderAddressTypes::SHIPPING,
                'country' => $shippingCountry->name,
                'state' => $shippingState->name,
                'label_as' => $preparedOrderData->shippingAddress->label_as,
                'address_line_1' => $preparedOrderData->shippingAddress->address_line_1,
                'zip_code' => $preparedOrderData->shippingAddress->zip_code,
                'city' => $preparedOrderData->shippingAddress->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'type' => OrderAddressTypes::BILLING,
                'country' => $billingCountry->name,
                'state' => $billingState->name,
                'label_as' => $preparedOrderData->billingAddress->label_as,
                'address_line_1' => $preparedOrderData->billingAddress->address_line_1,
                'zip_code' => $preparedOrderData->billingAddress->zip_code,
                'city' => $preparedOrderData->billingAddress->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        OrderAddress::insert($addressesToInsert);
    }
}
