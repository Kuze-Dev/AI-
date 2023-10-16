<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
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
