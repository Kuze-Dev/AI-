<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('redirect guest from [base url] to [login page]', function (bool $tenant) {

    if ($tenant) {
        testInTenantContext();
    }

    get('/')
        ->assertRedirect('admin');
})
    ->with(['central' => false, 'tenant' => true]);

it('redirect authenticated from [login page] to admin [dashboard page]', function (bool $tenant) {

    if ($tenant) {
        testInTenantContext();
    }

    loginAsSuperAdmin();

    get('admin/login')
        ->assertRedirect('admin');
})
    ->with(['central' => false, 'tenant' => true]);
