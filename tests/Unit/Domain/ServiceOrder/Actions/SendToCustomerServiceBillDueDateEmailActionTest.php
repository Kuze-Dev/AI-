<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Notifications\ServiceBillDueDateNotification;
use Illuminate\Support\Facades\Notification;

it('can execute', function () {
    testInTenantContext();

    Notification::fake();

    $customer = CustomerFactory::new()->make();

    app(SendToCustomerServiceBillDueDateEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()->for($customer)->make(),
            ServiceBillFactory::new()->make()
        );

    Notification::assertSentOnDemand(ServiceBillDueDateNotification::class);
});
