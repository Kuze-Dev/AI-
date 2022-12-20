<?php

declare(strict_types=1);

use App\Filament\Resources\TenantResource\Pages\EditTenant;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can render page', function () {
    $tenant = TenantFactory::new()->createOne();

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $tenant->name,
            'domains' => $tenant->domains->toArray(),
        ]);
});

it('can edit tenant', function () {
    $tenant = TenantFactory::new()->withDomains()->createOne();

    livewire(EditTenant::class, ['record' => $tenant->getKey()])
        ->fillForm([
            'name' => 'Test',
            'domains.0.domain' => 'test.localhost',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tenant::class, ['name' => 'Test']);
    assertDatabaseHas(Domain::class, ['domain' => 'test.localhost']);
    assertDatabaseCount(Domain::class, 1);
});
