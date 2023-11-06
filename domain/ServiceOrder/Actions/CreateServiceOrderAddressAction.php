<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAddressData;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Models\ServiceOrderAddress;

class CreateServiceOrderAddressAction
{
    public function execute(ServiceOrderAddressData $serviceOrderAddressData): void
    {
        $addressesToInsert = [];

        $serviceAddress = Address::whereId($serviceOrderAddressData->service_address_id)
            ->first();

        if ($serviceAddress instanceof Address) {
            $commonAddressData = [
                'service_order_id' => $serviceOrderAddressData
                    ->serviceOrder
                    ->id,
                'country' => $serviceAddress->state->country->name,
                'state' => $serviceAddress->state->name,
                'label_as' => $serviceAddress->label_as,
                'address_line_1' => $serviceAddress->address_line_1,
                'zip_code' => $serviceAddress->zip_code,
                'city' => $serviceAddress->city,
            ];

            $addressesToInsert = [
                [
                    'type' => ServiceOrderAddressType::SERVICE_ADDRESS,
                    ...$commonAddressData,
                ],
                [
                    'type' => ServiceOrderAddressType::BILLING_ADDRESS,
                    ...$commonAddressData,
                ],
            ];
        }

        $billingAddress = Address::whereId($serviceOrderAddressData->billing_address_id)
            ->first();

        if (
            ! $serviceOrderAddressData->is_same_as_billing &&
            $billingAddress instanceof Address
        ) {
            $addressesToInsert[1] = [
                'service_order_id' => $serviceOrderAddressData
                    ->serviceOrder
                    ->id,
                'type' => ServiceOrderAddressType::BILLING_ADDRESS,
                'country' => $billingAddress->state->country->name,
                'state' => $billingAddress->state->name,
                'label_as' => $billingAddress->label_as,
                'address_line_1' => $billingAddress->address_line_1,
                'zip_code' => $billingAddress->zip_code,
                'city' => $billingAddress->city,
            ];
        }

        ServiceOrderAddress::insert($addressesToInsert);
    }
}
