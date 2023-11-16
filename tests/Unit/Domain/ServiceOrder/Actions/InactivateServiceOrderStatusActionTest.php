<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\InactivateServiceOrderStatusAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;

beforeEach(function () {
    testInTenantContext();

    $this->serviceOrder = ServiceOrderFactory::new()->create();
});

it('can execute', function () {
    app(InactivateServiceOrderStatusAction::class)->execute($this->serviceOrder);

    expect($this->serviceOrder->status)->toBe(ServiceOrderStatus::INACTIVE);
});
