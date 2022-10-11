<?php

namespace Domain\Auth\Actions;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ConfirmPasswordAction
{
    public function execute(string $password, ?string $guard = null): bool
    {
        if (! Session::isStarted()) {
            return false;
        }

        Validator::validate(
            compact('password'),
            ['password' => [
                'required',
                "current_password:{$guard}",
            ]]
        );

        Session::put('auth.password_confirmed_at', time());

        return true;
    }
}
