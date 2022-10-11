<?php

declare(strict_types=1);

use Tests\Fixtures\User;

it('can get two factor holder name', function () {
    $user = User::make(['email' => 'test@user']);

    $twoFactorHolder = $user->twoFactorHolder();

    expect($twoFactorHolder)->toBeString();
});
