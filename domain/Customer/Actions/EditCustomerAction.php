<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Domain\Tier\Enums\TierApprovalStatus;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class EditCustomerAction
{
    public function __construct(private readonly SyncMediaCollectionAction $syncMediaCollection)
    {
    }

    public function execute(Customer $customer, CustomerData $customerData): mixed
    {
        $customer->update(array_filter([
            'tier_id' => $customerData->tier_id,
            'email' => $customerData->email,
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'gender' => $customerData->gender,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
            'tier_approval_status' => $customerData->tier_approval_status,
            'register_status' => $customerData->register_status,
        ]));

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

        if($customer->register_status == RegisterStatus::REJECTED) {
            app(SendRejectedEmailAction::class)->execute($customer);
        }

        if($customer->tier_approval_status == TierApprovalStatus::APPROVED) {
            app(SendApprovedEmailAction::class)->execute($customer);
        }

        return $customer;
    }
}
