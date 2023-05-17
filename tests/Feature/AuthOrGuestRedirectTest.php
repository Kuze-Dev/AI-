<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('redirect guest from [base url] to [login page]')
    ->get('/')
    ->assertRedirect('admin/login');

it('redirect authenticated from [login page] to admin [dashboard page]', function () {

    loginAsSuperAdmin();

    get('admin/login')
        ->assertRedirect('admin');
});
