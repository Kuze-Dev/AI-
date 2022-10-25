<?php

declare(strict_types=1);

use Domain\Tenant\Actions\DeleteTenantAction;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;

use function Pest\Laravel\assertDatabaseCount;

it('can delete tenant', function () {
    /** @var Tenant $tenant */
    $tenant = TenantFactory::new()
        ->withDomains('test')
        ->createOne();

    $tenant = app(DeleteTenantAction::class)->execute($tenant);

    assertDatabaseCount(Tenant::class, 0);
});
