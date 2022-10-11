<?php

namespace Domain\Auth\Actions;

use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Illuminate\Pipeline\Pipeline;

class LoginAction
{
    public function execute(LoginData $loginData): LoginResult
    {
        return (new Pipeline(app()))
            ->send($loginData)
            ->through(config('domain.auth.login.pipeline'))
            ->then(fn () => LoginResult::SUCCESS);
    }
}
