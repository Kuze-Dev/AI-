<?php

declare(strict_types=1);

use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Database\Factories\StateFactory;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\Taxation\Database\Factories\TaxZoneFactory;
use Domain\Taxation\Enums\PriceDisplay;

beforeEach(function () {
    testInTenantContext();
});

it('can get info', function () {

    $subTotal = fake()->randomFloat(2, 1, 100);

    $tax = TaxZoneFactory::new(['is_active' => true])
        ->isDefault()
        ->has(CountryFactory::new())
        ->createOne();

    $country = $tax->countries->first();

    $address = AddressFactory::new()
        ->has(
            StateFactory::new(['country_id', $country->id])
        )
        ->createOne();

    $taxPercentage = $tax->percentage;

    $taxPriceDisplay = $tax->price_display;

    if ($taxPriceDisplay === PriceDisplay::EXCLUSIVE) {
        $taxTotal = $subTotal * ($taxPercentage / 100.0);
        $totalPrice = $subTotal + $taxTotal;
    } else {
        $taxTotal = 0;
        $totalPrice = $subTotal;
    }

    $data = app(GetTaxableInfoAction::class)
        ->execute(
            $subTotal,
            $address
        );

    expect($taxTotal)->toBe($data['taxTotal']);

    expect($totalPrice)->toBe($data['totalPrice']);
});
