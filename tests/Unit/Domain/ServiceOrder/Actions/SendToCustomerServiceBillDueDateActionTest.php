<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\Testing\QueueableActionFake;

beforeEach(function () {
    testInTenantContext();
});

it('can dispatch', function () {
    Queue::fake();

    app(SendToCustomerServiceBillDueDateAction::class)
        ->onQueue()
        ->execute(
            CustomerFactory::new()->make(),
            ServiceBillFactory::new()->make()
        );

    QueueableActionFake::assertPushed(SendToCustomerServiceBillDueDateAction::class);
});
