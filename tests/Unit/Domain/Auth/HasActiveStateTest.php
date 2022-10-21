<?php

declare(strict_types=1);

use Domain\Auth\Notifications\ActivateAccount;
use Illuminate\Contracts\Notifications\Dispatcher;
use Mockery\MockInterface;
use Tests\Fixtures\User;

it('can check if user is active', function () {
    $admin = new User(['active' => true]);

    expect($admin->isActive())->toBeTrue();
});

it('can send activate account notification', function () {
    $user = User::make(['email' => 'test@user']);
    $this->mock(
        Dispatcher::class,
        fn (MockInterface $mock) => $mock->expects('send')
            ->with($user, ActivateAccount::class)
    );

    $user->sendActivateAccountNotification();
});
