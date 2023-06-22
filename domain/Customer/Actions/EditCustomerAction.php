<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Domain\Support\Common\Actions\SyncMediaCollectionAction;
use Domain\Support\Common\DataTransferObjects\MediaCollectionData;
use Domain\Support\Common\DataTransferObjects\MediaData;

class EditCustomerAction
{
    public function __construct(private readonly SyncMediaCollectionAction $syncMediaCollection)
    {
    }

    public function execute(Customer $customer, CustomerData $customerData): Customer
    {
        $customer->update([
            'email' => $customerData->email,
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
        ]);

        $this->syncMediaCollection->execute($customer, new MediaCollectionData(
            collection: 'image',
            media: [
                new MediaData(media: $customerData->image),
            ],
        ));

        return $customer;
    }
}
