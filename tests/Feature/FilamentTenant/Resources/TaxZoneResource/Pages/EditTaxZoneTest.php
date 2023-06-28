<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxZoneResource\Pages\EditTaxZone;
use Domain\Taxation\Database\Factories\TaxZoneFactory;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $taxZone = TaxZoneFactory::new()->createOne();

    livewire(EditTaxZone::class, ['record' => $taxZone->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $taxZone->name,
            'price_display' => $taxZone->price_display->value,
            'is_active' => $taxZone->is_active,
            'is_default' => $taxZone->is_default,
            'type' => $taxZone->type->value,
            'percentage' => $taxZone->percentage,
        ])
        ->assertOk();
});

it('can edit', function () {
    $taxZone = TaxZoneFactory::new()->createOne();

    livewire(EditTaxZone::class, ['record' => $taxZone->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'price_display' => PriceDisplay::EXCLUSIVE,
            'is_active' => true,
            'is_default' => true,
            'type' => TaxZoneType::COUNTRY,
            'percentage' => 12.000,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($taxZone->refresh())
        ->name->toBe('Test')
        ->price_display->toBe(PriceDisplay::EXCLUSIVE)
        ->is_active->toBe(true)
        ->is_default->toBe(true)
        ->type->toBe(TaxZoneType::COUNTRY)
        ->percentage->toBe(12);
});

todo('can edit tax zone by country');

todo('can edit tax zone by state');
