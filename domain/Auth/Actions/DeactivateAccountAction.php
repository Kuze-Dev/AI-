<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\HasActiveState;
use Domain\Auth\Events\Deactivated;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class DeactivateAccountAction
{
    public function execute(User&HasActiveState $user): ?bool
    {
        if (! $user->isActive()) {
            return null;
        }

        $user->forceFill(['active' => false])
            ->save();

        Event::dispatch(new Deactivated($user));

        return true;
    }
}
