<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;

class UpdatePasswordAction
{
    public function execute(User $user, string $password): bool
    {
        $user->fill(['password' => $password]);

        $user->setRememberToken(Str::random(60));

        return $user->save();
    }
}
