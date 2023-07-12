<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderAddress;

class CreateOrderAddressAction
{
    public function execute(Order $order, PreparedOrderData $preparedOrderData)
    {
        $addressesToInsert = [
            [
                'order_id' => $order->id,
                'type' => 'Shipping',
                'state' =>  $preparedOrderData->shipping_address->state ? $preparedOrderData->shipping_address->state->name : null,
                'label_as' =>  $preparedOrderData->shipping_address->label_as,
                'address_line_1' => $preparedOrderData->shipping_address->address_line_1,
                'zip_code' => $preparedOrderData->shipping_address->zip_code,
                'city' => $preparedOrderData->shipping_address->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'type' => 'Billing',
                'state' =>  $preparedOrderData->billing_address->state ? $preparedOrderData->billing_address->state->name : null,
                'label_as' => $preparedOrderData->shipping_address->label_as,
                'address_line_1' => $preparedOrderData->billing_address->address_line_1,
                'zip_code' => $preparedOrderData->billing_address->zip_code,
                'city' => $preparedOrderData->billing_address->city,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        OrderAddress::insert($addressesToInsert);
    }
}
