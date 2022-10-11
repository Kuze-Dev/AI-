<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class VerifyEmailAction
{
    public function execute(MustVerifyEmail $user): ?bool
    {
        if ($user->hasVerifiedEmail()) {
            return null;
        }

        return tap(
            $user->markEmailAsVerified(),
            fn () => event(new Verified($user))
        );
    }
}
