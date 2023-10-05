<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\CalculateServiceOrderTotalPriceAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;

beforeEach(function () {
    testInTenantContext();
});

it('can test', function () {
    $servicePrice = ServiceOrderFactory::new()
        ->createOne()
        ->total_price;

    $additionalCharges = [
        new ServiceOrderAdditionalChargeData(
            price: 100,
            quantity: 1
        ),
        new ServiceOrderAdditionalChargeData(
            price: 100,
            quantity: 2
        ),
    ];

    $totalPrice = money($servicePrice);

    foreach ($additionalCharges as $charge) {
        $totalPrice = $totalPrice->add($charge->price * $charge->quantity);
    }

    $total = app(CalculateServiceOrderTotalPriceAction::class)
        ->execute(
            $servicePrice,
            $additionalCharges
        );

    expect($total->getAmount())
        ->toBe($totalPrice->getAmount());
});
