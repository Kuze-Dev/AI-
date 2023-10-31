<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use App\Settings\ServiceSettings;
use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Events\AdminServiceOrderStatusUpdatedEvent;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ChangeByAdminNotification;
use Illuminate\Support\Facades\Notification;

class AdminServiceOrderStatusUpdatedListener
{
    public function __construct(
        /**
         * TODO: to be used for sendToCustomerServiceOrderStatusAction.
         *
         * @phpstan-ignore-next-line */
        private Customer $customer,
        private ServiceOrder $serviceOrder,
        private CreateServiceBillAction $createServiceBillAction,
        private SendToCustomerServiceOrderStatusAction $sendToCustomerServiceOrderStatusAction,
        private ServiceSettings $serviceSettings,
        private bool $shouldNotifyCustomer = false,
    ) {
    }

    public function handle(
        AdminServiceOrderStatusUpdatedEvent $event
    ): void {

        $this->customer = $event->customer;

        $this->serviceOrder = $event->serviceOrder;

        $this->shouldNotifyCustomer = $event->shouldNotifyCustomer;

        $this->prepareServiceBill();

        $this->notifyCustomer();

        $this->notifyAdmin();
    }

    private function prepareServiceBill(): void
    {
        if (
            $this->serviceOrder->status === ServiceOrderStatus::FORPAYMENT &&
            is_null(
                $this->serviceOrder
                    ->latestServiceBill()
            )
        ) {
            $this->createServiceBillAction
                ->execute(
                    ServiceBillData::fromCreatedServiceOrder(
                        $this->serviceOrder->toArray()
                    )
                );
        }
    }

    private function notifyCustomer(): void
    {
        if ($this->shouldNotifyCustomer) {
            $this->sendToCustomerServiceOrderStatusAction
                ->execute($this->serviceOrder);
        }
    }

    private function notifyAdmin(): void
    {
        if (
            $this->serviceSettings->admin_should_receive &&
            filled($this->serviceSettings->admin_main_receiver)
        ) {
            Notification::route(
                'mail',
                $this->serviceSettings->admin_main_receiver
            )->notify(
                new ChangeByAdminNotification(
                    $this->serviceOrder,
                    $this->serviceOrder->status->value
                )
            );
        }
    }
}
