<?php

declare(strict_types=1);

use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

Http::fake([
    'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json' => Http::response('[{"iso2": "US", "name": "United States", "capital": "Washington", "timezones": [{"gmtOffsetName": "GMT-4"}], "states": [{"name": "California"}]}]'),
]);

assertDatabaseHas('countries', [
    'code' => 'US',
    'name' => 'United States',
    'capital' => 'Washington',
    'timezone' => 'GMT-4',
    'active' => false,
]);

assertDatabaseHas('states', [
    'name' => 'California',
]);
