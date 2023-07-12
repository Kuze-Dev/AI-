<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxZoneResource\Pages\CreateTaxZone;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Taxation\Database\Factories\TaxZoneFactory;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateTaxZone::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create', function () {
    livewire(CreateTaxZone::class)
        ->fillForm([
            'name' => 'Test',
            'price_display' => PriceDisplay::EXCLUSIVE,
            'is_active' => true,
            'is_default' => true,
            'type' => TaxZoneType::COUNTRY,
            'percentage' => 12.000,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(TaxZone::class, [
        'name' => 'Test',
        'price_display' => PriceDisplay::EXCLUSIVE,
        'is_active' => true,
        'is_default' => true,
        'type' => TaxZoneType::COUNTRY,
        'percentage' => 12.000,
    ]);
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

it('can create tax zone by country', function () {
    $countries = CountryFactory::new()->count(5)->create();
    $selectedCountries = $countries->random(2);

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
    ]);
    foreach ($selectedCountries as $selectedCountry) {
        assertDatabaseHas('tax_zone_country', [
            'tax_zone_id' => $record->getKey(),
            'country_id' => $selectedCountry->getKey(),
        ]);
    }
});

todo('can create tax zone by state');
