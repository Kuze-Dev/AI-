<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use App\Settings\CustomerSettings;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\DataTransferObjects\CustomerNotificationData;
use Domain\Customer\Enums\CustomerEvent;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Mail\CustomerRegisteredNotification;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Illuminate\Support\Facades\Mail;

readonly class ImportCustomerAction
{
    public function __construct(
        private CreateCustomerAction $createCustomerAction,
        private EditCustomerAction $editCustomerAction,
    ) {
    }

    public function execute(array $row): void
    {

        $customer = Customer::whereEmail($row['email'])
            ->withTrashed()
            ->first();

        if ($customer !== null && $customer->trashed()) {

            unset($row);

            return;
        }

        $data = CustomerData::fromArrayImportByAdmin(
            customerPassword: isset($row['registered']) && $row['registered'] != '' ? (string) $row['password'] : $customer?->password,
            tierKey: isset($row['tier'])
                ? Tier::whereName($row['tier'])->first()?->getKey()
                : null,
            row: $row,
            customerStatus: isset($row['registered']) && $row['registered'] != '' ? RegisterStatus::REGISTERED : RegisterStatus::UNREGISTERED

        );

        unset($row);

        if ($customer === null) {
            $customer = $this->createCustomerAction->execute($data);
            if (! empty(app(CustomerSettings::class)->customer_email_notifications)) {
                //customer imported event.
                $importedNotification = array_filter(app(CustomerSettings::class)->customer_email_notifications, function ($mail_notification) {
                    return $mail_notification['events'] == CustomerEvent::IMPORTED->value;
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

                return;

            }
        }

        $this->editCustomerAction->execute($customer, $data);

    }
}
