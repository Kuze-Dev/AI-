<?php

declare(strict_types=1);

use Domain\Auth\Actions\VerifyEmailAction;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Pest\Mock\Mock;
use Tests\Fixtures\User;

beforeEach(function () {
    Event::fake();
});

it('can verify email', function () {
//    $user = (new Mock(new User(['id' => 1, 'email' => 'test@user'])))
//        ->expect(markEmailAsVerified: fn () => true);
    $user = mock_expect(new User(['id' => 1, 'email' => 'test@user']),markEmailAsVerified: fn () => true);

    $result = app(VerifyEmailAction::class)->execute($user);

    expect($result)->toBeTrue();
    Event::assertDispatched(Verified::class);
});

it('does nothing when already verified', function () {
    $user = new User([
        'id' => 1,
        'email' => 'test@user',
        'email_verified_at' => now(),
    ]);

    $result = app(VerifyEmailAction::class)->execute($user);

    expect($result)->toBeNull();
    Event::assertNotDispatched(Verified::class);
});
