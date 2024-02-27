<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class CreateCustomerAction
{
    public function __construct(
        private readonly SyncMediaCollectionAction $syncMediaCollection,
        private readonly CreateAddressAction $createAddress,
    ) {
    }

    public function execute(CustomerData $customerData): Customer
    {
        $customer = $this->create($customerData);

        if ($customerData->shipping_address_data !== null) {
            $this->createAddress
                ->execute(
                    AddressData::fromAddressAddCustomer(
                        $customer,
                        $customerData->shipping_address_data
                    )
                );
        }

        if ($customerData->billing_address_data !== null) {
            $this->createAddress
                ->execute(
                    AddressData::fromAddressAddCustomer(
                        $customer,
                        $customerData->billing_address_data
                    )
                );
        }

        if ($customerData->image !== null) {
            $this->syncMediaCollection->execute($customer, new MediaCollectionData(
                collection: 'image',
                media: [
                    new MediaData(media: $customerData->image),
                ],
            ));
        }

        if ($customer->register_status === RegisterStatus::REGISTERED) {
            event(new Registered($customer));
        }

        return $customer;
    }

    private function create(CustomerData $customerData): Customer
    {
        if (
            $customerData->through_api_registration &&
            ($customer = self::createThroughRegistrationAPI($customerData)) !== null
        ) {
            return $customer;
        }

        return Customer::create([
            'tier_id' => $customerData->tier_id,
            'email' => $customerData->email,
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'gender' => $customerData->gender,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
            'email_verification_type' => $customerData->email_verification_type,
            'register_status' => $customerData->register_status,
            'tier_approval_status' => $customerData->tier_approval_status,
        ]);
    }

    private static function createThroughRegistrationAPI(CustomerData $customerData): ?Customer
    {
        $customer = Customer::whereEmail($customerData->email)->first();

        if ($customer === null) {
            return null;
        }

        $customer->update([
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'gender' => $customerData->gender,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
            'email_verification_type' => $customerData->email_verification_type,
            'register_status' => $customerData->register_status,
        ]);

        return $customer;
    }
}
