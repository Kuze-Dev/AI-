<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Models\Country;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can update country active', function () {
    $country = CountryFactory::new()->createOne([
        'active' => true,
    ]);

    $country->update(['active' => false]);

    assertDatabaseHas(Country::class, [
        'id' => $country->id,
        'active' => false,
    ]);

});
