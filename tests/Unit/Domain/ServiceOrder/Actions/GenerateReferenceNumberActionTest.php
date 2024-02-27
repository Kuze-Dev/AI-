<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\GenerateReferenceNumberAction;
use Domain\ServiceOrder\Models\ServiceOrder;

it('can generate', function () {
    testInTenantContext();

    $reference = app(GenerateReferenceNumberAction::class)
        ->execute(ServiceOrder::class);

    expect($reference)->toBe('SO'.now()->format('ymd').'0001');
});
