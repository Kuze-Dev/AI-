<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\SendToCustomerServiceBillEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Notifications\ServiceBillNotification;
use Illuminate\Support\Facades\Notification;

it('can execute', function () {
    testInTenantContext();

    Notification::fake();

    app(SendToCustomerServiceBillEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()->make(),
            ServiceBillFactory::new()->make()
        );

    Notification::assertSentOnDemand(ServiceBillNotification::class);
});
