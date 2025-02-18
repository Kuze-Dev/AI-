<?php

declare(strict_types=1);

use Domain\Auth\Actions\DeactivateAccountAction;
use Domain\Auth\Events\Deactivated;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\User;
beforeEach()->skip('skip otp');

it('can deactivate account', function () {
    Event::fake();

    $user = User::create([
        'email' => 'test@user',
        'active' => true,
    ]);

    $result = app(DeactivateAccountAction::class)->execute($user);

    expect($result)->toBeTrue();
    Event::assertDispatched(Deactivated::class);
});

it('does nothing when already deactived', function () {
    Event::fake();

    $user = User::create([
        'email' => 'test@user',
        'active' => false,
    ]);

    $result = app(DeactivateAccountAction::class)->execute($user);

    expect($result)->toBeNull();
    Event::assertNotDispatched(Deactivated::class);
});
