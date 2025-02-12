<?php

declare(strict_types=1);

use Domain\Auth\Actions\ResetPasswordAction;
use Domain\Auth\DataTransferObjects\ResetPasswordData;
use Domain\Auth\Enums\PasswordResetResult;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Password;
use Pest\Mock\Mock;

it('can reset password', function () {
    Event::fake();
//    $passwordBroker = (new Mock(PasswordBroker::class))
//        ->expect(reset: function (array $credentials, callable $callback) {
//            $user = (new Mock(User::class))
//                ->expect(
//                    fill: fn () => null,
//                    setRememberToken: fn () => null,
//                    save: fn () => true
//                );
//
//            $callback($user, $credentials['password']);
//
//            return PasswordBroker::PASSWORD_RESET;
//        });
    $passwordBroker = mock_expect(PasswordBroker::class,reset: function (array $credentials, callable $callback) {
//        $user = (new Mock(User::class))
//            ->expect(
//                fill: fn () => null,
//                setRememberToken: fn () => null,
//                save: fn () => true
//            );

        $user = mock_expect(User::class,
            fill: fn () => null,
            setRememberToken: fn () => null,
            save: fn () => true
        );

        $callback($user, $credentials['password']);

        return PasswordBroker::PASSWORD_RESET;
    });

    Password::shouldReceive('broker')
        ->once()
        ->andReturn($passwordBroker);

    $result = app(ResetPasswordAction::class)->execute(new ResetPasswordData(
        email: 'test@user',
        password: 'secret',
        token: 'token',
    ));

    Event::assertDispatched(PasswordReset::class);

    expect($result)->toEqual(PasswordResetResult::PASSWORD_RESET);
});
