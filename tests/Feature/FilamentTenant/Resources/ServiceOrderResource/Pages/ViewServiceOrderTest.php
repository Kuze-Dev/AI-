<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderAddressFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(ServiceBase::class);

    loginAsSuperAdmin();
});

it('can view', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->for(
            ServiceFactory::new()
                ->isSubscription()
                ->isActive()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->has(ServiceOrderAddressFactory::new()->billing())
        ->has(ServiceOrderAddressFactory::new()->service())
        ->createOne();

    livewire(ViewServiceOrder::class, ['record' => $serviceOrder->getRouteKey()])
        ->assertOk();
});
