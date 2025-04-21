<?php

declare(strict_types=1);

use App\Filament\Resources\TenantResource\Pages\ListTenants;
use Domain\Tenant\Database\Factories\TenantFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can render page', function () {
    livewire(ListTenants::class)->assertSuccessful();
});

it('can list tenants', function () {
    $tenants = TenantFactory::new()
        ->withDomains()
        ->count(5)
        ->create();

    livewire(ListTenants::class)->assertCanSeeTableRecords($tenants);
});

it('can delete tenant', function () {
    $tenant = TenantFactory::new()
        ->withDomains()
        ->createOne();
    $domain = $tenant->domains->first();

    livewire(ListTenants::class)->callTableAction(DeleteAction::class, $tenant);

    assertModelMissing($tenant);
    assertModelMissing($domain);
});
