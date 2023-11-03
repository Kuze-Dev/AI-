<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ViewServiceOrder;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext()->features()->activate(ServiceBase::class);

    Filament::setContext('filament-tenant');

    loginAsSuperAdmin();
});

it('can view', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->for(
            ServiceFactory::new()
                ->isSubscription()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->createOne();

    livewire(ViewServiceOrder::class, ['record' => $serviceOrder->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertOk()
        ->assertSee([
            $serviceOrder->service_name,
            $serviceOrder->service_price,
            $serviceOrder->service->billing_cycle,
            $serviceOrder->service->due_date_every,
            $serviceOrder->customer_first_name,
            $serviceOrder->customer_last_name,
            $serviceOrder->customer_email,
            $serviceOrder->customer_mobile,
        ]);
});
