<?php

declare(strict_types=1);

use Domain\Auth\Actions\ForgotPasswordAction;
use Domain\Auth\Enums\PasswordResetResult;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;

it('can send password reset', function () {
    Password::shouldReceive('broker')
        ->once()
        ->andReturn(
            mock(PasswordBroker::class)
                ->expect(sendResetLink: fn (array $credentials) => PasswordBroker::RESET_LINK_SENT)
        );

    $result = app(ForgotPasswordAction::class)
        ->execute('test@user');

    expect($result)->toEqual(PasswordResetResult::RESET_LINK_SENT);
});
