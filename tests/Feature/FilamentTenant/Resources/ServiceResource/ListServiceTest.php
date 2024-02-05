<?php

declare(strict_types=1);

namespace Tests\Feature\FilamentTenant\Resources\ServiceResource;

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceResource\Pages\ListServices;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Filament\Pages\Actions\DeleteAction;
use Support\MetaData\Database\Factories\MetaDataFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(ServiceBase::class);
    loginAsSuperAdmin();

    CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);
});

it('can render page', function () {
    livewire(ListServices::class)
        ->assertOk();
});

it('can list services', function () {
    $services = ServiceFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    livewire(ListServices::class)
        ->assertCanSeeTableRecords($services)
        ->assertOk();
});

it('can delete services', function () {
    $service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->has(MetaDataFactory::new())
        ->createOne();

    livewire(ListServices::class)
        ->callTableAction(DeleteAction::class, $service)
        ->assertOk();

});
