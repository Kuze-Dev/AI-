<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\CreateServiceBillingsAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    testInTenantContext();
});

it('can dispatch', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->withPaidServiceBill()
                ->has(ServiceBillFactory::new()->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    // Queue::assertPushed(CreateServiceBillJob::)
})
    ->only();

it('can dispatch active only');
