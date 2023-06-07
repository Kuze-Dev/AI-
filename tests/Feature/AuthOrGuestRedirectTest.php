<?php

declare(strict_types=1);

use Filament\Facades\Filament;

use function Pest\Laravel\get;

it('redirect guest from [base url] to [login page]', function (bool $tenant) {

    if ($tenant) {
        testInTenantContext();
        Filament::setContext('filament-tenant');
    }

    get('/')
        ->assertRedirect('admin');
})
    ->with(['central' => false, 'tenant' => true]);

it('redirect authenticated from [login page] to admin [dashboard page]', function (bool $tenant) {

    if ($tenant) {
        testInTenantContext();
        Filament::setContext('filament-tenant');
    }

    loginAsSuperAdmin();

    get('admin/login')
        ->assertRedirect('admin');
})
    ->with(['central' => false, 'tenant' => true]);
