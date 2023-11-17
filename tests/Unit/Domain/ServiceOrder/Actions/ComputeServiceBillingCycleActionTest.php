<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\ComputeServiceBillingCycleAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();
});

it('can compute', function () {
    $data = app(ComputeServiceBillingCycleAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->createOne(),
            now()
        );

    assertInstanceOf(ServiceOrderBillingAndDueDateData::class, $data);
});
