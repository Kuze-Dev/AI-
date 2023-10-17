<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Events\ServiceBillDueDateNotificationSentEvent;
use Domain\ServiceOrder\Notifications\ServiceBillDueDateNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\Testing\QueueableActionFake;

beforeEach(function () {
    testInTenantContext();
});

it('can dispatch', function () {
    Queue::fake();

    app(SendToCustomerServiceBillDueDateEmailAction::class)
        ->onQueue()
        ->execute(
            CustomerFactory::new()->make(),
            ServiceBillFactory::new()->make()
        );

    QueueableActionFake::assertPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('can execute', function () {
    Notification::fake();

    $customer = CustomerFactory::new()->make();

    app(SendToCustomerServiceBillDueDateEmailAction::class)
        ->execute(
            $customer,
            ServiceBillFactory::new()->make()
        );

    Notification::assertSentTo(
        [$customer],
        ServiceBillDueDateNotification::class
    );
});
