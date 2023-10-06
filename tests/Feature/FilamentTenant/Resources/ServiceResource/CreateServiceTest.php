<?php

use App\FilamentTenant\Resources\ServiceResource\Pages\CreateService;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Service\Models\Service;
use Filament\Facades\Filament;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateService::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create form', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $service = livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => "Daily",
            'due_date_every' => 20,
            'is_featured' => false,
            'is_special_offer' => false,
            'pay_upfront' => false,
            'is_subscription' => false,
            'status' => 'active'
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Service::class, [
        'name' => 'Test',
        'blueprint_id' => $blueprint->getKey(),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => $service->name,
            'model_type' => $service->getMorphClass(),
            'model_id' => $service->getKey(),
        ]
    );
});

