<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\UpdateServiceOrderAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderData;

beforeEach(function () {
    testInTenantContext();
});

it('can execute', function () {

    $serviceOrder = ServiceOrderFactory::new()
        ->createOne();

    app(UpdateServiceOrderAction::class)
        ->execute(
            $serviceOrder,
            new UpdateServiceOrderData(
                additional_charges: [],
                customer_form: []
            )
        );

    expect($serviceOrder->additional_charges)
        ->toBe([]);

    expect($serviceOrder->customer_form)
        ->toBe([]);
});
