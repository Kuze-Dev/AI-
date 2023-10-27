<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\ExpiredServiceOrderAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;

beforeEach(function () {
    testInTenantContext();
});

/** TODO: to be removed. */
it('can update', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->active()
        ->has(
            ServiceBillFactory::new()
                ->forPayment()
        )
        ->createOne();

    app(ExpiredServiceOrderAction::class)->execute($serviceOrder);

    expect($serviceOrder->status)->toBe(ServiceOrderStatus::INACTIVE);
});
