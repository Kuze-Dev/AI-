<?php

declare(strict_types=1);

use Domain\Tenant\Actions\CreateTenantAction;
use Domain\Tenant\DataTransferObjects\TenantData;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;

it('can create tenant', function () {
    $tenant = app(CreateTenantAction::class)->execute(TenantData::fromArray([
        'name' => 'Test',
        'domains' => [
            ['domain' => 'test.com'],
        ],
    ]));

    assertModelExists($tenant);
    assertDatabaseHas(Domain::class, ['domain' => 'test.com']);
});
