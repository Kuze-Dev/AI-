<?php

declare(strict_types=1);

use Domain\Auth\Actions\ActivateAccountAction;
use Domain\Auth\Events\Activated;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\User;

beforeEach()->skip('skip otp');

it('can activate account', function () {
    Event::fake();

    $user = User::create([
        'email' => 'test@user',
        'active' => false,
    ]);

    $result = app(ActivateAccountAction::class)->execute($user);

    expect($result)->toBeTrue();
    Event::assertDispatched(Activated::class);
});

it('does nothing when already active', function () {
    Event::fake();

    $user = User::create([
        'email' => 'test@user',
        'active' => true,
    ]);

    $result = app(ActivateAccountAction::class)->execute($user);

    expect($result)->toBeNull();
    Event::assertNotDispatched(Activated::class);
});
