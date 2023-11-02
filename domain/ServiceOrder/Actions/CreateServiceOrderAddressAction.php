<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAddressActionData;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Models\ServiceOrderAddress;

class CreateServiceOrderAddressAction
{
    public function execute(
        ServiceOrderAddressActionData $serviceOrderAddressActionData
    ): void {

        $addressesToInsert = [];

        if (
            $serviceAddressModel = Address::whereId(
                $serviceOrderAddressActionData->service_address_id
            )->first()
        ) {
            $commonAddressData = [
                'service_order_id' => $serviceOrderAddressActionData
                    ->serviceOrder
                    ->id,
                'country' => $serviceAddressModel->state->country->name,
                'state' => $serviceAddressModel->state->name,
                'label_as' => $serviceAddressModel->label_as,
                'address_line_1' => $serviceAddressModel->address_line_1,
                'zip_code' => $serviceAddressModel->zip_code,
                'city' => $serviceAddressModel->city,
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

        if (
            ! $serviceOrderAddressActionData->is_same_as_billing &&
            $billingAddressModel = Address::whereId(
                $serviceOrderAddressActionData->billing_address_id
            )->first()
        ) {
            $addressesToInsert[1] = [
                'service_order_id' => $serviceOrderAddressActionData
                    ->serviceOrder
                    ->id,
                'type' => ServiceOrderAddressType::BILLING_ADDRESS,
                'country' => $billingAddressModel->state->country->name,
                'state' => $billingAddressModel->state->name,
                'label_as' => $billingAddressModel->label_as,
                'address_line_1' => $billingAddressModel->address_line_1,
                'zip_code' => $billingAddressModel->zip_code,
                'city' => $billingAddressModel->city,
            ];
        }

        ServiceOrderAddress::insert($addressesToInsert);
    }
}
