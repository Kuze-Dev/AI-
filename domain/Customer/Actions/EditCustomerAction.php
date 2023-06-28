<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class EditCustomerAction
{
    public function __construct(private readonly SyncMediaCollectionAction $syncMediaCollection)
    {
    }

    public function execute(Customer $customer, CustomerData $customerData): Customer
    {
        $newData = [
            'tier_id' => $customerData->tier_id,
            'email' => $customerData->email,
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
        ];

        if ($newData['email'] === null) {
            unset($newData['email']);
        }

        $customer->update($newData);
        unset($newData);

        if ($customerData->image !== null) {
            $this->syncMediaCollection->execute($customer, new MediaCollectionData(
                collection: 'image',
                media: [
                    new MediaData(media: $customerData->image),
                ],
            ));
        }

        if ($customer->wasChanged('email')) {
            $customer->forceFill(['email_verified_at' => null])
                ->save();

            $customer->sendEmailVerificationNotification();
        }

        return $customer;
    }
}
