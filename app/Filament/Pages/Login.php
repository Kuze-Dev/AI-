<?php

declare(strict_types=1);

namespace App\Filament\Pages;

class Login extends \Filament\Pages\Auth\Login
{
    //    public function authenticate(): ?LoginResponse
    //    {
    //        $data = $this->form->getState();
    //
    //        $result = app(LoginAction::class)->execute(new LoginData(
    //            email: $data['email'],
    //            password: $data['password'],
    //            remember: $data['remember'] ?? false,
    //            guard: 'admin',
    //        ));
    //
    //        return new class($result) implements LoginResponse
    //        {
    //            public function __construct(
    //                protected LoginResult $result
    //            ) {
    //            }
    //
    //            public function toResponse($request): mixed
    //            {
    //                return match ($this->result) {
    //                    LoginResult::TWO_FACTOR_REQUIRED => redirect()->route('filament.auth.two-factor'),
    //                    LoginResult::SUCCESS => redirect()->intended(Filament::getUrl()),
    //                };
    //            }
    //        };
    //    }
}
