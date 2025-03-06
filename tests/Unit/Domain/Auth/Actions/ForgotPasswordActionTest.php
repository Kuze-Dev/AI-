<?php

declare(strict_types=1);

use Domain\Auth\Actions\ForgotPasswordAction;
use Domain\Auth\Enums\PasswordResetResult;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Pest\Mock\Mock;

beforeEach()->skip('skip otp');

it('can send password reset', function () {
    Password::shouldReceive('broker')
        ->once()
        ->andReturn(
            //            (new Mock(PasswordBroker::class))
            //                ->expect(sendResetLink: fn (array $credentials) => PasswordBroker::RESET_LINK_SENT)
            mock_expect(PasswordBroker::class, sendResetLink: fn (array $credentials) => PasswordBroker::RESET_LINK_SENT)
        );

    $result = app(ForgotPasswordAction::class)->execute('test@user');

    expect($result)->toEqual(PasswordResetResult::RESET_LINK_SENT);
});
