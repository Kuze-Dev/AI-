<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Notifications\ServiceBillNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    testInTenantContext();
});

it('can execute', function () {
    Notification::fake();

    $customer = CustomerFactory::new()->make();

    app(SendToCustomerServiceBillEmailAction::class)
        ->execute(
            $customer,
            ServiceBillFactory::new()->make()
        );

    Notification::assertSentTo(
        [$customer],
        ServiceBillNotification::class
    );
});
