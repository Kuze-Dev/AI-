<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Domain\Support\Common\Actions\SyncMediaCollectionAction;
use Domain\Support\Common\DataTransferObjects\MediaCollectionData;

class CreateCustomerAction
{
    public function __construct(private readonly SyncMediaCollectionAction $syncMediaCollection)
    {
    }

    public function execute(CustomerData $customerData): Customer
    {
        $customer = Customer::create([
            'email' => $customerData->email,
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
        ]);

        $this->syncMediaCollection->execute($customer, MediaCollectionData::fromArray([
            'collection' => 'image',
            'media' => [
                'media' => $customerData->image,
            ],
        ]));

        return $customer;
    }
}
