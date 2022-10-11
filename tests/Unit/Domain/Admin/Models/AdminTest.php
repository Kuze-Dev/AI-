<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Illuminate\Support\Facades\Hash;

it('can check if user is super admin', function () {
    Admin::unguard();
    $admin = new Admin(['id' => 1]);
    Admin::reguard();

    expect($admin->isSuperAdmin())->toBeTrue();
});

it('can check if user is active', function () {
    $admin = new Admin(['active' => true]);

    expect($admin->isActive())->toBeTrue();
});

it('can get user\'s full name', function () {
    $admin = new Admin([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($admin->full_name)->toEqual('John Doe');
});

it('can set user\'s password', function () {
    $admin = new Admin([
        'password' => 'secret',
    ]);

    expect(Hash::check('secret', $admin->password))->toBeTrue();
});
