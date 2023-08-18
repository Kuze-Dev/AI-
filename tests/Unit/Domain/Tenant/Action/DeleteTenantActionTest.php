<?php

declare(strict_types=1);

use Domain\Tenant\Actions\DeleteTenantAction;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;

use function Pest\Laravel\assertModelMissing;

it('can delete tenant', function () {
    /** @var Tenant $tenant */
    $tenant = TenantFactory::new()
        ->withDomains('test')
        ->createOne();

    app(DeleteTenantAction::class)->execute($tenant);

    assertModelMissing($tenant);
});
