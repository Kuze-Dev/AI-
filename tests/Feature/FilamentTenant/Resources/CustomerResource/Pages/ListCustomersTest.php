<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\CustomerResource\Pages\ListCustomers;
use Domain\Customer\Database\Factories\CustomerFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\ForceDeleteAction;
use Filament\Pages\Actions\RestoreAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertCount;

uses()->group('customer');

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
    $tenant->features()->activate(AddressBase::class);
    $tenant->features()->activate(TierBase::class);
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListCustomers::class)
        ->assertOk();
});

it('can list customers', function () {
    $customers = CustomerFactory::new()
        ->count(3)
        ->create();

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers)
        ->assertOk();
});

it('can delete customer', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    livewire(ListCustomers::class)
        ->callTableAction(DeleteAction::class, $customer)
        ->assertOk();

    assertSoftDeleted($customer);
});

it('can restore customer', function () {
    $customer = CustomerFactory::new()
        ->deleted()
        ->createOne();

    livewire(ListCustomers::class)
        ->callTableAction(RestoreAction::class, $customer)
        ->assertOk();

    assertNotSoftDeleted($customer);
});

it('can force delete customer', function () {
    $customer = CustomerFactory::new()
        ->deleted()
        ->hasAddress()
        ->createOne();

    $customer->refresh();

    assertCount(1, $customer->addresses);

    livewire(ListCustomers::class)
        ->callTableAction(ForceDeleteAction::class, $customer)
        ->assertOk();

    assertModelMissing($customer);
    assertModelMissing($customer->addresses->first());
});
