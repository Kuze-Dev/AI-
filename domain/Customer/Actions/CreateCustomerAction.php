<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use App\Settings\CustomerSettings;
use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\DataTransferObjects\CustomerNotificationData;
use Domain\Customer\Enums\CustomerEvent;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Mail\CustomerRegisteredNotification;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class CreateCustomerAction
{
    public function __construct(
        private readonly SyncMediaCollectionAction $syncMediaCollection,
        private readonly CreateAddressAction $createAddress,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
    ) {
    }

    public function execute(CustomerData $customerData): Customer
    {
        $customer = $this->create($customerData);

        if (app(CustomerSettings::class)->blueprint_id) {
            $this->createBlueprintDataAction->execute($customer);
        }

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
            'username' => $customerData->username,
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
            'data' => $customerData->data,
        ]);
    }

    private static function createThroughRegistrationAPI(CustomerData $customerData): ?Customer
    {
        $customer = Customer::whereEmail($customerData->email)->first();

        if ($customer === null) {
            return null;
        }

        $customer->update(array_filter([
            'first_name' => $customerData->first_name,
            'last_name' => $customerData->last_name,
            'mobile' => $customerData->mobile,
            'status' => $customerData->status,
            'username' => $customerData->username,
            'gender' => $customerData->gender,
            'birth_date' => $customerData->birth_date,
            'password' => $customerData->password,
            'email_verification_type' => $customerData->email_verification_type,
            'register_status' => $customerData->register_status,
        ]));

        if (! empty(app(CustomerSettings::class)->customer_email_notifications)) {

            $importedNotification = array_filter(app(CustomerSettings::class)->customer_email_notifications, function ($mail_notification) {
                return $mail_notification['events'] == CustomerEvent::REGISTERED->value;
            });

            if (! empty($importedNotification)) {
                foreach ($importedNotification as $notification) {
                    Mail::send(new CustomerRegisteredNotification(
                        $customer,
                        CustomerNotificationData::fromarray($notification),
                        $customer->data ?? []
                    )
                    );
                }
            }
        }

        return $customer;
    }
}
