<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Address\Actions\CreateCustomerAddressAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class CreateCustomerAction
{
    public function __construct(
        private readonly SyncMediaCollectionAction $syncMediaCollection,
        private readonly CreateCustomerAddressAction $createCustomerAddress,
    ) {
    }

    public function execute(CustomerData $customerData): Customer
    {
        $customer = Customer::create([
            'tier_id' => $customerData->tier_id,
            'cuid' => Str::uuid(),
            'email' => $customerData->email,
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'gender' => $customerData->gender,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
        ]);

        if ($customerData->shipping_address_data !== null) {
            $this->createCustomerAddress
                ->execute($customer, $customerData->shipping_address_data);
        }

        if ($customerData->billing_address_data !== null) {
            $this->createCustomerAddress
                ->execute($customer, $customerData->billing_address_data);
        }

        if ($customerData->image !== null) {
            $this->syncMediaCollection->execute($customer, new MediaCollectionData(
                collection: 'image',
                media: [
                    new MediaData(media: $customerData->image),
                ],
            ));
        }

        event(new Registered($customer));

        return $customer;
    }
}
