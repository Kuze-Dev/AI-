<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\UpdateServiceBillAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceBillData;

beforeEach(function () {
    testInTenantContext();
});

it('can execute', function () {

    $serviceBill = ServiceBillFactory::new()
        ->createOne();

    app(UpdateServiceBillAction::class)
        ->execute(
            $serviceBill,
            new UpdateServiceBillData(
                sub_total: fake()->randomDigit(),
                tax_total: fake()->randomDigit(),
                total_amount: fake()->randomDigit(),
                additional_charges: [],
            )
        );

    expect($serviceBill->additional_charges)
        ->toBe([]);
});
