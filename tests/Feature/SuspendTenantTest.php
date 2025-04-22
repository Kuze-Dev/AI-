<?php

declare(strict_types=1);

use Domain\Tenant\TenantSupport;

use function Pest\Laravel\get;

it('tenant Can be Suspended test', function () {

    testInTenantContext();

    $tenant = TenantSupport::model();

    $tenant->is_suspended = true;

    $tenant->save();

    get('admin/login')->assertForbidden();

});
