<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\UpdateServiceOrderStatusAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderStatusData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;

it('can execute', function () {
    testInTenantContext();

    $serviceOrder = ServiceOrderFactory::new()->create();

    $serviceOrder = app(UpdateServiceOrderStatusAction::class)->execute(
        $serviceOrder,
        new UpdateServiceOrderStatusData(
            service_order_status: ServiceOrderStatus::ACTIVE
        )
    );

    expect($serviceOrder->status)->toBe(ServiceOrderStatus::ACTIVE);
});
