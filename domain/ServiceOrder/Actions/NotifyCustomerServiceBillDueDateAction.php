<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use App\Settings\ServiceSettings;
use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Exceptions\MissingServiceSettingsConfigurationException;
use Domain\ServiceOrder\Models\ServiceOrder;

class NotifyCustomerServiceBillDueDateAction
{
    public function __construct(
        private SendToCustomerServiceBillDueDateEmailAction $sendToCustomerServiceBillDueDateAction,
        private ServiceSettings $serviceSettings
    ) {
    }

    public function execute(): void
    {
        $customers = Customer::query()
            ->with([
                'serviceOrders' => fn ($query) => $query->whereActive()
                    ->whereSubscriptionBased(),
                'serviceOrders.serviceBills' => fn ($query) => $query->whereForPaymentStatus()
                    ->whereNotifiable(),
            ])
            ->whereActive()
            ->whereRegistered()
            ->whereHas('serviceOrders.serviceBills', fn ($query) => $query->whereForPaymentStatus()
                ->whereNotifiable())
            ->get();

        /** @var int|null $daysBeforeDueDateNotification */
        $daysBeforeDueDateNotification = $this->serviceSettings
            ->days_before_due_date_notification;

        if (is_null($daysBeforeDueDateNotification)) {
            throw new MissingServiceSettingsConfigurationException();
        }

        $customers
            ->each(
                function (Customer $customer) use ($daysBeforeDueDateNotification) {
                    $customer
                        ->serviceOrders
                        ->each(
                            function (ServiceOrder $serviceOrder) use (
                                $customer,
                                $daysBeforeDueDateNotification,
                            ) {

                                /** @var \Domain\ServiceOrder\Models\ServiceBill $latestForPaymentServiceBill */
                                $latestForPaymentServiceBill = $serviceOrder->latestForPaymentServiceBill();

                                /** @var \Carbon\Carbon $dateOfNotification */
                                $dateOfNotification = now()->parse($latestForPaymentServiceBill->due_date)
                                    ->subDays($daysBeforeDueDateNotification)
                                    ->toDateString();

                                /** @var \Carbon\Carbon $dateToday */
                                $dateToday = now()->toDateString();

                                /** @var \Carbon\Carbon $billDate */
                                $billDate = now()->parse($latestForPaymentServiceBill->bill_date)
                                    ->toDateString();

                                if (
                                    ($dateOfNotification < $billDate &&
                                        $billDate === $dateToday) ||
                                    ($dateOfNotification === $dateToday)
                                ) {
                                    $this->sendToCustomerServiceBillDueDateAction
                                        ->onQueue()
                                        ->execute(
                                            $customer,
                                            $latestForPaymentServiceBill
                                        );
                                }

                            }
                        );
                }
            );
    }
}
