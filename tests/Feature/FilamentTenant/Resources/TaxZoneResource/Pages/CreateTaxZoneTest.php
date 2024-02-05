<?php

declare(strict_types=1);

use App\Features\Shopconfiguration\TaxZone as ShopconfigurationTaxZone;
use App\FilamentTenant\Resources\TaxZoneResource\Pages\CreateTaxZone;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Country;
use Domain\Taxation\Database\Factories\TaxZoneFactory;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(ShopconfigurationTaxZone::class);

    CountryFactory::new()
        ->count(3)
        ->has(StateFactory::new()->count(3))
        ->create();
});

it('can render page', function () {
    livewire(CreateTaxZone::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create tax zone by country', function () {
    $selectedCountries = Country::inRandomOrder()->limit(2)->get();

    $record = livewire(CreateTaxZone::class)
        ->assertFormFieldIsHidden('countries')
        ->fillForm([
            'name' => 'Test',
            'price_display' => PriceDisplay::EXCLUSIVE,
            'type' => TaxZoneType::COUNTRY,
            'percentage' => 12.000,
        ])
        ->assertFormFieldIsVisible('countries')
        ->fillForm(['countries' => $selectedCountries->modelKeys()])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(TaxZone::class, [
        'name' => 'Test',
        'is_default' => true,
        'type' => TaxZoneType::COUNTRY,
    ]);
    foreach ($selectedCountries as $selectedCountry) {
        assertDatabaseHas('tax_zone_country', [
            'tax_zone_id' => $record->getKey(),
            'country_id' => $selectedCountry->getKey(),
        ]);
    }
});

it('can create tax zone by state', function () {
    $selectedCountry = Country::inRandomOrder()->first();
    $selectedStates = $selectedCountry->states->random(2);

    $record = livewire(CreateTaxZone::class)
        ->assertFormFieldIsHidden('countries')
        ->fillForm([
            'name' => 'Test',
            'price_display' => PriceDisplay::EXCLUSIVE,
            'type' => TaxZoneType::STATE,
            'percentage' => 12.000,
        ])
        ->assertFormFieldIsVisible('countries')
        ->assertFormFieldIsVisible('states')
        ->fillForm([
            'countries' => [$selectedCountry->getKey()],
            'states' => $selectedStates->modelKeys(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(TaxZone::class, [
        'name' => 'Test',
        'is_default' => true,
        'type' => TaxZoneType::STATE,
    ]);
    assertDatabaseHas('tax_zone_country', [
        'tax_zone_id' => $record->getKey(),
        'country_id' => $selectedCountry->getKey(),
    ]);
    foreach ($selectedStates as $selectedState) {
        assertDatabaseHas('tax_zone_state', [
            'tax_zone_id' => $record->getKey(),
            'state_id' => $selectedState->getKey(),
        ]);
    }
});

it('can create and override old default', function () {
    /** @var TaxZone $initialDefaultTaxZone */
    $initialDefaultTaxZone = TaxZoneFactory::new()
        ->isDefault()
        ->createOne();

    livewire(CreateTaxZone::class)
        ->fillForm([
            'name' => 'Test',
            'price_display' => PriceDisplay::EXCLUSIVE,
            'is_active' => true,
            'is_default' => true,
            'type' => TaxZoneType::COUNTRY,
            'countries' => Country::inRandomOrder()->limit(2)->pluck('id')->toArray(),
            'percentage' => 12.000,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(TaxZone::class, [
        'name' => 'Test',
        'is_default' => true,
    ]);
    expect($initialDefaultTaxZone->refresh())->is_default->toBeFalse();
});
