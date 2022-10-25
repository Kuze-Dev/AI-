<?php

use Domain\Tenant\Actions\UpdateTenantAction;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('can update tenant', function () {
    /** @var Tenant $tenant */
    $tenant = TenantFactory::new()
        ->withDomains('test')
        ->createOne();

    $tenant = app(UpdateTenantAction::class)->execute($tenant, new TenantData(
        name: 'Test',
        domains: ['test.com'],
    ));

    assertDatabaseHas(Tenant::class, ['name' => 'Test']);
    assertDatabaseHas(Domain::class, ['domain' => 'test.com']);
    assertDatabaseCount(Domain::class, 1);
});
