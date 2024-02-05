<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('tenant Can be Suspended test', function () {

    testInTenantContext();

    $tenant = tenancy()->tenant;

    $tenant->is_suspended = true;

    $tenant->save();

    get('admin/login')->assertStatus(403);

});
