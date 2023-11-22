<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\GenerateServiceTransactionReceiptAction;
use Domain\ServiceOrder\Database\Factories\ServiceTransactionFactory;

it('can generate', function () {
    testInTenantContext();

    app(GenerateServiceTransactionReceiptAction::class)
        ->execute(
            $serviceTransaction = ServiceTransactionFactory::new()
                ->createOne()
        );

    expect(
        $serviceTransaction->serviceOrder
            ->customer
            ->getMedia('receipts')
            ->count()
    )->toBe(1);
});
